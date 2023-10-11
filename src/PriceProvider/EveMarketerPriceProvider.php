<?php

namespace RecursiveTree\Seat\EveMarketerPriceProvider\PriceProvider;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use JsonException;
use RecursiveTree\Seat\EveMarketerPriceProvider\EveMarketerPriceProviderServiceProvider;
use RecursiveTree\Seat\PricesCore\Contracts\IPriceable;
use RecursiveTree\Seat\PricesCore\Contracts\IPriceProviderBackend;
use RecursiveTree\Seat\PricesCore\Exceptions\PriceProviderException;
use RecursiveTree\Seat\PricesCore\Utils\UserAgentBuilder;

class EveMarketerPriceProvider implements IPriceProviderBackend
{

    /**
     * Fetches the prices for the items in $items
     * Implementations should store the computed price directly on the Priceable object using the setPrice method.
     * In case an error occurs, a PriceProviderException should be thrown, so that an error message can be shown to the user.
     *
     * @param Collection<IPriceable> $items The items to appraise
     * @param array $configuration The configuration of this price provider backend.
     * @throws PriceProviderException
     */
    public function getPrices(Collection $items, array $configuration): void
    {
        // step 1: Collect TypeIDs we are interested in
        $typeIDs = [];
        foreach ($items as $item){
            $typeIDs[$item->getTypeID()] = null;
        }

        // step 2: http query setup
        $user_agent = (new UserAgentBuilder())
            ->seatPlugin(EveMarketerPriceProviderServiceProvider::class)
            ->defaultComments()
            ->build();
        $client = new \GuzzleHttp\Client([
            'base_uri' => 'https://api.evemarketer.com',
            'timeout' => $configuration['timeout'],
            'headers' => [
                'User-Agent' => $user_agent,
            ]
        ]);
        $query_base = [];
        if($configuration['region']){
            $query_base['regionlimit'] = $configuration['region'];
        }
        if($configuration['system']){
            $query_base['usesystem'] = $configuration['system'];
        }

        // step 3: evemarketer requests
        // since evemarketer has a 200 typeIDs/request limit, chunk if necessary
        collect(array_keys($typeIDs))
            ->chunk(200)
            ->each(function ($chunk) use ($client, $configuration, &$typeIDs, $query_base){
                try {
                    $query_base['typeid'] = implode(',',$chunk->toArray());
                    $response = $client->post('/ec/marketstat/json?typeid=1,2', [
                        'query'=>$query_base
                    ]);
                    //dd(str($response->getBody()));
                    $response = json_decode($response->getBody(), false, 64, JSON_THROW_ON_ERROR);
                } catch (GuzzleException | JsonException $e) {
                    throw new PriceProviderException('Failed to load data from evemarketer: '.$e->getMessage(),0,$e);
                }

                foreach ($response as $item){
                    if($configuration['is_buy']) {
                        $price_bucket = $item->buy;
                    } else {
                        $price_bucket = $item->sell;
                    }

                    $variant = $configuration['variant'];
                    if($variant == 'min'){
                        $price = $price_bucket->min;
                    } elseif ($variant == 'max') {
                        $price = $price_bucket->max;
                    } elseif ($variant == 'avg') {
                        $price = $price_bucket->avg;
                    } elseif ($variant == 'median') {
                        $price = $price_bucket->median;
                    } elseif ($variant == 'wavg') {
                        $price = $price_bucket->wavg;
                    } else {
                        $price = $price_bucket->fivePercent;
                    }

                    $typeIDs[$price_bucket->forQuery->types[0]] = $price;
                }
            });


        // step 4: Feed prices back to system
        foreach ($items as $item){
            $price = $typeIDs[$item->getTypeID()] ?? null;
            if($price === null) {
                throw new PriceProviderException('EvePraisal didn\'t respond with the requested prices.');
            }
            if(!(is_int($price) || is_float($price))){
                throw new PriceProviderException('EvePraisal responded with a non-numerical price.');
            }

            $item->setPrice($price * $item->getAmount());
        }
    }
}