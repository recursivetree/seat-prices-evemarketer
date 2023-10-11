<?php

namespace RecursiveTree\Seat\EveMarketerPriceProvider\Http\Controllers;

use Illuminate\Http\Request;
use RecursiveTree\Seat\PricesCore\Models\PriceProviderInstance;
use Seat\Eveapi\Models\Sde\Region;
use Seat\Eveapi\Models\Sde\SolarSystem;
use Seat\Web\Http\Controllers\Controller;

class EveMarketerPriceProviderController extends Controller
{
    public function configuration(Request $request){
        $existing = PriceProviderInstance::find($request->id);

        $name = $request->name ?? $existing->name;
        $id = $request->id;
        $timeout = $existing->configuration['timeout'] ?? 5;
        $is_buy = $existing->configuration['is_buy'] ?? false;
        $price_variant = $existing->configuration['variant'] ?? 'min';
        $region = Region::find($existing->configuration['region'] ?? null);
        $system = SolarSystem::find($existing->configuration['system'] ?? null);
        $is_universe = $existing->configuration['universe'] ?? null;

        //dd($region, $system);

        return view('evemarketerpriceprovider::configuration', compact('name', 'is_universe', 'id', 'timeout','system', 'is_buy', 'price_variant', 'region'));
    }

    public function configurationPost(Request $request) {
        $request->validate([
            'id'=>'nullable|integer',
            'name'=>'required|string',
            'timeout'=>'required|integer',
            'price_type' => 'required|string|in:sell,buy',
            'price_variant' => 'required|string|in:wavg,avg,min,max,median,fivePercent',
            'location'=>'required|in:universe,system,region',
            'system'=>'required_if:location,system|integer',
            'region'=>'required_if:location,region|integer',
        ]);

        $model = PriceProviderInstance::findOrNew($request->id);
        $model->name = $request->name;
        $model->backend = 'recursivetree/seat-prices-evemarketer';
        $model->configuration = [
            'timeout' => $request->timeout,
            'is_buy' => $request->price_type === 'buy',
            'variant' => $request->price_variant,
            'system' => $request->system && $request->location === 'system' ? intval($request->system) : null,
            'region' => $request->region && !$request->system && $request->location === 'region'  ? intval($request->region) : null,
            'universe' => $request->location === 'universe' ? true:null,
        ];
        $model->save();

        return redirect()->route('pricescore::settings')->with('success',trans('evemarketerpriceprovider::evemarketer.edit_price_provider_success'));
    }
}