@extends('layouts.registered')

@section('content')
    <section class="document-page gradiented gradient-accent">
        <setup inline-template :model="{{$booking}}" :date-fields="{{$date_fields}}" :locations="{{$locations}}" v-cloak>
        <div id="booking">
            @include('bookings.partials.header')
            @include('bookings.partials.shipments')
            <fieldset :disabled="model.status_id === 2">
                <template v-if="isActive('setup')">
                    @include('bookings.partials.setup')
                </template>
                <template v-else-if="isActive('dates')">
                    @include('bookings.partials.dates')
                </template>
                <template v-else-if="isActive('statuses')">
                    @include('bookings.partials.statuses')
                </template>
            </fieldset>
            <div class="text-center bottom_spacing" v-if="model.status_id !== 2">
                <a href="{{route('bookings.bookings')}}" class="btn btn-default">Cancel</a>
                <button class="btn btn-success" @click="save">Save</button>
            </div>
        </div>
    </setup>
    </section>
@endsection

@push('head')
    <link rel="stylesheet" href="{{mix('css/bootstrap-extend.css')}}">
    <link rel="stylesheet" href="{{mix('css/trades.css')}}">
@endpush

@push('scripts')
    <script src="{{mix('/js/general/select-search.js')}}"></script>
    <script src="{{mix('/js/general/info-popup.js')}}"></script>
    <script src="{{mix('/js/bookings/setup.js')}}"></script>
@endpush
