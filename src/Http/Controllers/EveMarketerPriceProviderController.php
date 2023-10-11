<?php

namespace RecursiveTree\Seat\EveMarketerPriceProvider\Http\Controllers;

use Illuminate\Http\Request;
use RecursiveTree\Seat\PricesCore\Models\PriceProviderInstance;
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

        return view('evemarketerpriceprovider::configuration', compact('name', 'id', 'timeout', 'is_buy', 'price_variant'));
    }

    public function configurationPost(Request $request) {
        $request->validate([
            'id'=>'nullable|integer',
            'name'=>'required|string',
            'timeout'=>'required|integer',
            'price_type' => 'required|string|in:sell,buy',
            'price_variant' => 'required|string|in:wavg,avg,min,max,median,fivePercent',
        ]);

        $model = PriceProviderInstance::findOrNew($request->id);
        $model->name = $request->name;
        $model->backend = 'recursivetree/seat-prices-evemarketer';
        $model->configuration = [
            'timeout' => $request->timeout,
            'is_buy' => $request->price_type === 'buy',
            'variant' => $request->price_variant,
        ];
        $model->save();

        return redirect()->route('pricescore::settings')->with('success',trans('evemarketerpriceprovider::evemarketer.edit_price_provider_success'));
    }
}