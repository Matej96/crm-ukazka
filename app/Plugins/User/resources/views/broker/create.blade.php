@extends('layouts.app')

@section('breadcrumb')
    <x-breadcrumb-row :actions="array_merge($division ? [
                        route('user.division.index') => \App\Plugins\User\app\Models\Division::getBreadcrumbClassName(\App\Plugins\User\app\Models\Division::class),
                        route('user.division.show', $division) => \App\Plugins\User\app\Models\Division::getBreadcrumbModelName($division),
                        route('user.division.show', $division) . '#brokers-tab' => \App\Plugins\User\app\Models\Broker::getBreadcrumbClassName(\App\Plugins\User\app\Models\Broker::class),
                    ] : [
                        route('user.broker.index') => \App\Plugins\User\app\Models\Broker::getBreadcrumbClassName(\App\Plugins\User\app\Models\Broker::class),
                    ])" :active="__('Create')"
                      tag-name="{{ $division ? __('forms.division_code') : null }}"
                      tag-id="{{ $division ? $division->code : null }}"
    ></x-breadcrumb-row>
@endsection

@section('content')
    <div class="container">
        <div class="mb-3 text-center">
            <h1 class="display-6 mb-3">{{ __('Broker') }}</h1>
        </div>
        <form method="POST" action="{{ $division ? route('user.division.broker.store', [$division]) : route('user.broker.store') }}"
              data-user-birth-autocomplete="true"
              data-company-autocomplete="true"
              data-broker-form="true"
              enctype="multipart/form-data"
        >
            @csrf
            @include('plugin.User::broker._broker_form', ['formFields' => $formFields])
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            <a href="{{ $division ? route('user.division.show', [$division]) . '#brokers-tab' : route('user.broker.index') }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
        </form>
    </div>
@endsection

@push('scripts')
{{--  !! '_script_company_autocomplete' musi byt pred 'plugin.User::broker._script_broker_form' !! --}}
    @include('_script_company_finstat_autocomplete', ['triggers' => ['business_id', 'business_name']])
    @include('_script_user_birth_autocomplete', ['triggers' => ['birth_id'], 'autocomplete_columns' => []])
    @include('_script_user_birth_autocomplete', ['triggers' => ['representative[_pending_][birth_id]'], 'autocomplete_columns' => [
        'birth_id'      => 'representative[_pending_][birth_id]',
        'birth_date'    => 'representative[_pending_][birth_date]',
        'gender'        => 'representative[_pending_][gender]',
    ]])
    @include('plugin.User::broker._script_broker_form')
    <script>
        {!! JsValidator::formRequest(App\Plugins\User\app\Http\Requests\BrokerStoreRequest::class, 'form')->view('layouts.jsvalidation') !!};
    </script>
@endpush
