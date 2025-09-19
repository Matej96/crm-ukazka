@extends('layouts.app')

@section('breadcrumb')
    <x-breadcrumb-row :actions="array_merge($division ? [
                        route('user.division.index') => \App\Plugins\User\app\Models\Division::getBreadcrumbClassName(\App\Plugins\User\app\Models\Division::class),
                        route('user.division.show', $division) => \App\Plugins\User\app\Models\Division::getBreadcrumbModelName($division),
                        route('user.division.show', $division) . '#brokers-tab' => \App\Plugins\User\app\Models\Broker::getBreadcrumbClassName(\App\Plugins\User\app\Models\Broker::class),
                        route('user.division.broker.show', [$division, $broker]) => \App\Plugins\User\app\Models\Broker::getBreadcrumbModelName($broker),
                    ] : [
                        route('user.broker.index') => \App\Plugins\User\app\Models\Broker::getBreadcrumbClassName(\App\Plugins\User\app\Models\Broker::class),
                        route('user.broker.show', $broker) => \App\Plugins\User\app\Models\Broker::getBreadcrumbModelName($broker),
                    ])" :active="__('Edit')"
                      tag-name="{{ __('forms.career_id') }}"
                      tag-id="{{ $broker->career_id }}"
    >
        @if($broker->career_status === 'not_active')
            <span class="badge fw-semibold px-3 text-bg-danger">
                {{ \App\Plugins\User\app\Models\Broker::getFormFieldOptions('career_status', ['without_filters' => true])['career_status'][$broker->career_status] ?? $broker->career_status }}
            </span>
        @endif
    </x-breadcrumb-row>
@endsection


@section('content')
    <div class="container">
        <x-partials.broker.header :division="$division" :broker="$broker"></x-partials.broker.header>
        <form method="POST" action="{{ $division ? route('user.division.broker.update', [$division, $broker]) : route('user.broker.update', [$broker]) }}"
              data-user-birth-autocomplete="true"
              data-company-autocomplete="true"
              data-broker-form="true"
              enctype="multipart/form-data"
        >
            @method('PUT')
            @csrf
            @include('plugin.User::broker._broker_form', ['formFields' => $formFields])
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            @can('user.broker.delete')
                <button type="button" onclick="$('form.destroyForm').submit()" class="btn btn-danger">{{ __('Delete') }}</button>
            @endcan
            <a href="{{ $division ? route('user.division.broker.show', [$division, $broker]) : route('user.broker.show', $broker) }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
        </form>
        <form class="destroyForm" method="POST" action="{{ $division ? route('user.division.broker.destroy', [$division, $broker]) : route('user.broker.destroy', [$broker]) }}">
            @method('DELETE')
            @csrf
        </form>
    </div>
@endsection

@push('scripts')
    {{--  !! '_script_company_autocomplete' musi byt pred 'plugin.Client::client._script_client_form' !! --}}
    @include('_script_company_finstat_autocomplete', ['triggers' => ['business_id', 'business_name']])
    @include('_script_user_birth_autocomplete', ['triggers' => ['birth_id'], 'autocomplete_columns' => []])
    @include('_script_user_birth_autocomplete', ['triggers' => ['representative[_pending_][birth_id]'], 'autocomplete_columns' => [
        'birth_id'      => 'representative[_pending_][birth_id]',
        'birth_date'    => 'representative[_pending_][birth_date]',
        'gender'        => 'representative[_pending_][gender]',
    ]])
    @include('plugin.User::broker._script_broker_form')
    <script>
        {!! JsValidator::formRequest(App\Plugins\User\app\Http\Requests\BrokerUpdateRequest::class, 'form')->view('layouts.jsvalidation') !!};
    </script>
    <script>
        $(document).on('submit', 'form.destroyForm', function(e) {
            var form = this;
            e.preventDefault();
            confirm(null, (result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            }, null, null, '{{ __('Delete') }}', null, null, {
                customClass: {
                    confirmButton: 'btn btn-danger mx-1',
                    cancelButton: 'btn btn-outline-secondary mx-1'
                }
            });
        });
    </script>
@endpush
