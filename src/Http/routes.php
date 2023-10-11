<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['web', 'auth', 'locale'],
    'prefix' => '/prices-evemarketer',
    'namespace'=>'RecursiveTree\Seat\EveMarketerPriceProvider\Http\Controllers'
], function () {
    Route::get('/configuration')
        ->name('evemarketerpriceprovider::configuration')
        ->uses('EveMarketerPriceProviderController@configuration')
        ->middleware('can:pricescore.settings');

    Route::post('/configuration')
        ->name('evemarketerpriceprovider::configuration.post')
        ->uses('EveMarketerPriceProviderController@configurationPost')
        ->middleware('can:pricescore.settings');
});