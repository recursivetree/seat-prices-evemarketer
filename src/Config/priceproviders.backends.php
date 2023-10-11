<?php


use RecursiveTree\Seat\EveMarketerPriceProvider\PriceProvider\EveMarketerPriceProvider;

return [
    'recursivetree/seat-prices-evemarketer' => [
        'backend'=> EveMarketerPriceProvider::class,
        'label'=>'evemarketerpriceprovider::evemarketer.evemarketer_price_provider',
        'plugin'=>'recursivetree/seat-prices-evemarketer',
        'settings_route' => 'evemarketerpriceprovider::configuration',
    ]
];