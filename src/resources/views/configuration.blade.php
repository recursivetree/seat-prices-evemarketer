@extends('web::layouts.app')

@section('title', trans('evemarketerpriceprovider::evemarketer.edit_price_provider'))
@section('page_header', trans('evemarketerpriceprovider::evemarketer.edit_price_provider'))
@section('page_description', trans('evemarketerpriceprovider::evemarketer.edit_price_provider'))

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ trans('evemarketerpriceprovider::evemarketer.edit_price_provider') }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('evemarketerpriceprovider::configuration.post') }}" method="POST">
                @csrf
                <input type="hidden" name="id" value="{{ $id ?? "" }}">

                <div class="form-group">
                    <label for="name">{{ trans('pricescore::settings.name') }}</label>
                    <input required type="text" name="name" id="name" class="form-control" placeholder="{{ trans('pricescore::settings.name_placeholder') }}" value="{{ $name ?? '' }}">
                </div>

                <div class="form-group">
                    <label for="timeout">{{ trans('evemarketerpriceprovider::evemarketer.timeout') }}</label>
                    <input required type="number" name="timeout" id="timeout" class="form-control" placeholder="{{ trans('pricescore::settings.timeout_placeholder') }}" value="{{ $timeout ?? 5 }}" min="0" step="1">
                    <small class="text-muted">{{ trans('evemarketerpriceprovider::evemarketer.timeout_description') }}</small>
                </div>

                <div class="form-group">
                    <label for="price_type">{{ trans('evemarketerpriceprovider::evemarketer.price_type') }}</label>
                    <select name="price_type" id="price_type" class="form-control" required>
                        <option value="sell" @if(!$is_buy) selected @endif>{{ trans('evemarketerpriceprovider::evemarketer.sell') }}</option>
                        <option value="buy" @if($is_buy) selected @endif>{{ trans('evemarketerpriceprovider::evemarketer.buy') }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="price_variant">{{ trans('evemarketerpriceprovider::evemarketer.price_variant') }}</label>
                    <select name="price_variant" id="price_variant" class="form-control" required>
                        <option value="max" @if($price_variant==='max') selected @endif>{{ trans('evemarketerpriceprovider::evemarketer.max') }}</option>
                        <option value="min" @if($price_variant==='min') selected @endif>{{ trans('evemarketerpriceprovider::evemarketer.min') }}</option>
                        <option value="avg" @if($price_variant==='avg') selected @endif>{{ trans('evemarketerpriceprovider::evemarketer.avg') }}</option>
                        <option value="wavg" @if($price_variant==='wavg') selected @endif>{{ trans('evemarketerpriceprovider::evemarketer.wavg') }}</option>
                        <option value="median" @if($price_variant==='median') selected @endif>{{ trans('evemarketerpriceprovider::evemarketer.median') }}</option>
                        <option value="fivePercent" @if($price_variant==='fivePercent') selected @endif>{{ trans('evemarketerpriceprovider::evemarketer.fivePercent') }}</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">{{ trans('pricescore::priceprovider.save')  }}</button>
            </form>
        </div>
    </div>

@endsection
