@extends('layouts.app')

@section('breadcrumb')
    <x-breadcrumb-row :actions="array_merge($division ? [
                    route('user.division.index') => \App\Plugins\User\app\Models\Division::getBreadcrumbClassName(\App\Plugins\User\app\Models\Division::class),
                    route('user.division.show', $division) => \App\Plugins\User\app\Models\Division::getBreadcrumbModelName($division),
                    route('user.division.show', $division) . '#brokers-tab' => \App\Plugins\User\app\Models\Broker::getBreadcrumbClassName(\App\Plugins\User\app\Models\Broker::class),
                ] : [
                    route('user.broker.index') => \App\Plugins\User\app\Models\Broker::getBreadcrumbClassName(\App\Plugins\User\app\Models\Broker::class),
                ])" :active="\App\Plugins\User\app\Models\Broker::getBreadcrumbModelName($broker)"
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
        <ul class="nav nav-tabs mb-3 justify-content-center" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview-tab-pane" type="button" role="tab" aria-controls="overview-tab-pane" aria-selected="true">
                    {{ __('Overview') }}
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#detail-tab-pane" type="button" role="tab" aria-controls="detail-tab-pane" aria-selected="false">
                    {{ __('Detail') }}
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#production-tab-pane" type="button" role="tab" aria-controls="production-tab-pane" aria-selected="false">
                    {{ __('Production') }}
                </button>
            </li>
            @if(!$broker->hasRole('division'))
            @can('user.broker_sector.read')
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sectors-tab-pane" type="button" role="tab" aria-controls="sectors-tab-pane" aria-selected="false">
                        {{ __('Sectors') }}
                    </button>
                </li>
            @endcan
            @can('user.broker_relation.read')
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#relations-tab-pane" type="button" role="tab" aria-controls="relations-tab-pane" aria-selected="false">
                        {{ __('BrokerRelations') }}
                    </button>
                </li>
            @endcan
            @endif
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#accesses-tab-pane" type="button" role="tab" aria-controls="accesses-tab-pane" aria-selected="false">
                    {{ __('Accesses') }}
                </button>
            </li>
            @can('user.broker.update')
            @can('drive.file.read')
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#files-tab-pane" type="button" role="tab" aria-controls="files-tab-pane" aria-selected="false">
                        {{ __('Files') }}
                    </button>
                </li>
            @endcan
            @endcan
        </ul>
    </div>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="overview-tab-pane" role="tabpanel" aria-labelledby="overview-tab" tabindex="0">
            <div class="container mb-3">
                <div class="d-flex flex-wrap flex-column flex-sm-row gap-2 mb-3">
                    <span class="h2 mb-0">{{ __('Overview') }}</span>
                    <div class="ms-auto">
                        <a class="btn btn-info" onclick="showDatePickerModal()">{{__('Broker commission rate')}}</a>
                    </div>
                </div>
                <form data-broker-form="true">
{{--TODO iba vycuc                    @include('plugin.User::broker._broker_form', ['formFields' => $formFields])--}}
                </form>
            </div>
            <div class="container">
                <div class="row row-cols-1 row-cols-lg-2 g-lg-4 g-3">
                    <div class="col">
                        <x-widgets.production-chart :broker="$broker"></x-widgets.production-chart>
                    </div>
                    <div class="col">
                        <x-widgets.production-product-categories-chart :broker="$broker"></x-widgets.production-product-categories-chart>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="detail-tab-pane" role="tabpanel" aria-labelledby="detail-tab" tabindex="0">
            <div class="container mb-3">
                <ul class="nav flex-column flex-md-row gap-3 position-absolute">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link p-0 active" data-bs-toggle="tab" data-bs-target="#broker-detail-tab-pane" type="button" role="tab" aria-controls="broker-detail-tab-pane" aria-selected="true">
                            <span class="h2 mb-0">{{ __('Detail') }}</span>
                        </button>
                    </li>
                    @can('user.candidate.read')
                    @if($broker->candidate_id)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link p-0" data-bs-toggle="tab" data-bs-target="#candidate-form-tab-pane" type="button" role="tab" aria-controls="candidate-form-tab-pane" aria-selected="true">
                                <span class="h2 mb-0">{{ __('Candidate') }}</span>
                            </button>
                        </li>
                    @endif
                    @endcan
                </ul>
            </div>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="broker-detail-tab-pane" role="tabpanel" aria-labelledby="broker-detail-tab" tabindex="0">
                    <div class="container">
                        <div class="d-flex flex-wrap flex-column flex-sm-row gap-2 mb-3">
                            <span class="h2 mb-0 invisible">{{ __('Detail') }}</span>
                            @can('user.candidate.read')
                            @if($broker->candidate_id)
                                <span class="h2 mb-0 invisible">{{ __('Candidate') }}</span>
                            @endif
                            @endcan
                            <div class="ms-auto">
                                @can('user.broker.update')
                                    @if($broker->career_status == 'not_active')
                                        <button type="button" onclick="changeCareerStatus(true)" class="btn btn-success">{{ __('Activate') }}</button>
                                    @else
                                        <button type="button" onclick="changeCareerStatus()" class="btn btn-danger">{{ __('Deactivate') }}</button>
                                    @endif
                                    <a href="{{ $division ? route('user.division.broker.edit', [$division, $broker]) : route('user.broker.edit', $broker) }}" class="btn btn-secondary" >{{__('Edit')}}</a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <form data-broker-form="true">
                            @include('plugin.User::broker._broker_form', ['formFields' => $formFields])
                        </form>
                    </div>
                </div>
                @can('user.candidate.read')
                @if($broker->candidate_id)
                <div class="tab-pane fade" id="candidate-form-tab-pane" role="tabpanel" aria-labelledby="candidate-form-tab" tabindex="0">
                    <div class="container">
                        <div class="d-flex flex-wrap flex-column flex-sm-row gap-2 mb-3">
                            <span class="h2 mb-0 invisible">{{ __('Detail') }}</span>
                            <span class="h2 mb-0 invisible">{{ __('Candidate') }}</span>
                            <div class="ms-auto">
                                @can('user.candidate.update')
                                <button class='btn btn-primary' onclick='sendDocumentationEmail( {{ $broker->candidate_id }} )'> {{ __('Send documentation') }} </button>
                                @endcan
                            </div>
                        </div>
                        <form data-broker-form="true">
                            @include('plugin.User::broker._broker_form', ['formFields' => $candidateFormFields])
                        </form>
                    </div>
                </div>
                @endif
                @endcan
            </div>
        </div>
        <div class="tab-pane fade" id="production-tab-pane" role="tabpanel" aria-labelledby="production-tab" tabindex="0">
            <div class="container mb-3">
                <ul class="nav flex-column flex-md-row gap-3">
                    @can('client.client.read')
                    <li class="nav-item" role="presentation">
                        <button class="nav-link p-0 active" data-bs-toggle="tab" data-bs-target="#production-clients-tab-pane" type="button" role="tab" aria-controls="production-clients-tab-pane" aria-selected="true">
                            <span class="h2 mb-0">{{ __('Clients') }}</span>
                        </button>
                    </li>
                    @endcan
                    @can('contract.contract.read')
                    <li class="nav-item" role="presentation">
                        <button class="nav-link p-0" data-bs-toggle="tab" data-bs-target="#production-contracts-tab-pane" type="button" role="tab" aria-controls="production-contracts-tab-pane" aria-selected="false">
                            <span class="h2 mb-0">{{ __('Contracts') }}</span>
                        </button>
                    </li>
                    @endcan
                    @can('commission.broker_commission.read')
                    <li class="nav-item" role="presentation">
                        <button class="nav-link p-0" data-bs-toggle="tab" data-bs-target="#production-commissions-tab-pane" type="button" role="tab" aria-controls="production-commissions-tab-pane" aria-selected="false">
                            <span class="h2 mb-0">{{ __('Commissions') }}</span>
                        </button>
                    </li>
                    @endcan
                    @can('commission.storno_commission.read')
                    <li class="nav-item" role="presentation">
                        <button class="nav-link p-0" data-bs-toggle="tab" data-bs-target="#production-stornos-tab-pane" type="button" role="tab" aria-controls="production-stornos-tab-pane" aria-selected="false">
                            <span class="h2 mb-0">{{ __('Stornos') }}</span>
                        </button>
                    </li>
                    @endcan
                </ul>
            </div>
            <div class="tab-content">
                @can('client.client.read')
                    <div class="tab-pane fade show active" id="production-clients-tab-pane" role="tabpanel" aria-labelledby="production-clients-tab" tabindex="0">
                        <div class="container-fluid">
                            <x-datatable plugin="client" table="client"
                                         :filters="['broker_id' => $broker->id]"
                            ></x-datatable>
                        </div>
                    </div>
                @endif
                @can('contract.contract.read')
                    <div class="tab-pane fade" id="production-contracts-tab-pane" role="tabpanel" aria-labelledby="production-contracts-tab" tabindex="0">
                        <div class="container-fluid">
                            <x-datatable plugin="contract" table="contract"
                                         :filters="['broker_id' => $broker->id]"
                            ></x-datatable>
                        </div>
                    </div>
                @endif
                @can('commission.broker_commission.read')
                    <div class="tab-pane fade" id="production-commissions-tab-pane" role="tabpanel" aria-labelledby="production-commissions-tab" tabindex="0">
                        <div class="container-fluid">
                            <x-datatable plugin="commission" table="broker_commission"
                                         :actions="[]"
                                         :filters="['broker_id' => $broker->id]"
                            ></x-datatable>
                        </div>
                    </div>
                @endif
                @can('commission.storno_commission.read')
                    <div class="tab-pane fade" id="production-stornos-tab-pane" role="tabpanel" aria-labelledby="production-stornos-tab" tabindex="0">
                        <div class="container-fluid">
                            <x-datatable plugin="commission" table="storno_commission"
                                         :actions="[]"
                                         :filters="['broker_id' => $broker->id]"
                            ></x-datatable>
                        </div>
                    </div>
                @endcan
            </div>
        </div>
        @if(!$broker->hasRole('division'))
        @can('user.broker_sector.read')
        <div class="tab-pane fade" id="sectors-tab-pane" role="tabpanel" aria-labelledby="sectors-tab" tabindex="0">
            <div class="container">
                <div class="d-flex flex-wrap flex-column flex-sm-row gap-2 mb-3">
                    <ul class="nav flex-column flex-md-row gap-3">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link p-0 active" data-bs-toggle="tab" data-bs-target="#sectors-current-tab-pane" type="button" role="tab" aria-controls="sectors-current-tab-pane" aria-selected="true">
                                <span class="h2 mb-0">{{ __('Sectors') }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link p-0" data-bs-toggle="tab" data-bs-target="#sectors-history-tab-pane" type="button" role="tab" aria-controls="sectors-history-tab-pane" aria-selected="false">
                                <span class="h2 mb-0">{{ __('History') }}</span>
                            </button>
                        </li>
                    </ul>
                    <div class="ms-auto">
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="sectors-current-tab-pane" role="tabpanel" aria-labelledby="sectors-current-tab" tabindex="0">
                        <x-datatable plugin="user" table="broker_sector"
                                     :actions="['custom_show' => auth()->user()->can('user.broker_sector.read') ? ['text' => __('Detail'), 'class' => 'btn-info', 'href' => $division ? route('user.division.broker.broker-sector.show', [$division, $broker, ':broker_sector']) : route('user.broker.broker-sector.show', [$broker, ':broker_sector'])] : false,]"
                                     :exceptColumns="$broker->is_business != 'legal_person' ? [
                                        'garant_id'
                                     ] : []"
                                     :filters="[
                                        'broker_id' => $broker->id,
                                        'is_current' => 'current'
                                     ]">
                        </x-datatable>
                    </div>
                    <div class="tab-pane fade show" id="sectors-history-tab-pane" role="tabpanel" aria-labelledby="sectors-history-tab" tabindex="0">
                        <x-datatable plugin="user" table="broker_sector"
                                     :actions="['custom_show' => auth()->user()->can('user.broker_sector.read') ? ['text' => __('Detail'), 'class' => 'btn-info', 'href' => $division ? route('user.division.broker.broker-sector.show', [$division, $broker, ':broker_sector']) : route('user.broker.broker-sector.show', [$broker, ':broker_sector'])] : false,]"
                                     :exceptColumns="$broker->is_business != 'legal_person' ? [
                                        'garant_id'
                                     ] : []"
                                     :filters="[
                                        'broker_id' => $broker->id,
                                        'is_current' => 'not_current'
                                     ]">
                        </x-datatable>
                    </div>
                </div>
            </div>
        </div>
        @endcan
        @can('user.broker_relation.read')
        <div class="tab-pane fade" id="relations-tab-pane" role="tabpanel" aria-labelledby="relations-tab" tabindex="0">
            <div class="container mb-3">
                <div class="d-flex flex-wrap flex-column flex-sm-row gap-2 mb-3">
                    <span class="h2 mb-0">{{ __('BrokerRelations') }}</span>
                    @if(auth()->user()->can('user.broker_relation.create'))
                        <div class="ms-auto">
                            <a type="button" class="btn btn-primary" href="{{ $division ? route('user.division.broker.broker-relation.create', [$division, $broker]) : route('user.broker.broker-relation.create', [$broker]) }}">{{ __('Create') }}</a>
                        </div>
                    @endif
                </div>
            </div>
            <div class="container">
                <x-datatable plugin="user" table="broker_relation"
                             :filters="['broker_id' => $broker->id]"
                             :actions="[
                                'custom_edit' => auth()->user()->can('user.broker_relation.update') ? ['text' => __('Edit'), 'class' => 'btn-secondary', 'href' => $division ? route('user.division.broker.broker-relation.edit', [$division, ':_receiving_broker_id', ':broker_relation']) : route('user.broker.broker-relation.edit', [':_receiving_broker_id', ':broker_relation']), 'replace' => ['_receiving_broker_id']] : false,
                             ]"
                ></x-datatable>
            </div>
        </div>
        @endcan
        @endif
        <div class="tab-pane fade" id="accesses-tab-pane" role="tabpanel" aria-labelledby="accesses-tab" tabindex="0">
            <div class="container">
                <ul class="nav flex-column flex-md-row gap-3 position-absolute">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link p-0 active" data-bs-toggle="tab" data-bs-target="#accesses-users-tab-pane" type="button" role="tab" aria-controls="accesses-users-tab-pane" aria-selected="true">
                            <span class="h2 mb-0">{{ __('Users') }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link p-0" data-bs-toggle="tab" data-bs-target="#accesses-partners-tab-pane" type="button" role="tab" aria-controls="accesses-partners-tab-pane" aria-selected="false">
                            <span class="h2 mb-0">{{ __('Partners') }}</span>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="accesses-users-tab-pane" role="tabpanel" aria-labelledby="accesses-users-tab" tabindex="0">
                    <div class="container mb-3">
                        <div class="d-flex flex-wrap flex-column flex-sm-row gap-2 mb-3">
                            <div class="d-flex flex-wrap flex-column flex-md-row gap-3">
                                <span class="h2 mb-0 invisible">{{ __('Users') }}</span>
                                <span class="h2 mb-0 invisible">{{ __('Partners') }}</span>
                            </div>
                            <div class="ms-auto">
                                @can('user.broker.update')
                                    <button class="btn btn-primary" onclick="assignUser()">{{ __('Assign') }}</button>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <x-datatable plugin="system" table="user"
                                     :filters="['broker_id' => $broker->id]"
                                     :exceptColumns="['group_ids', 'created_at']"
                                     :actions="[
                                'create_broker' => auth()->user()->can('user.broker.create') ? ['text' => __('Create account'), 'href' => $division ? route('user.division.user.assignation', [$division->id, ':user']) : route('user.user.assignation', [':user'])] : false,
                                'unassign' => auth()->user()->can('user.broker.update') ? ['text' => __('Unassign'), 'onclick' => 'unassignUser(\':user\')', 'class' => 'btn-danger'] : false,
                         ]"></x-datatable>
                    </div>
                </div>
                <div class="tab-pane fade" id="accesses-partners-tab-pane" role="tabpanel" aria-labelledby="accesses-partners-tab" tabindex="0">
                    <div class="container">
                        <div class="d-flex flex-wrap flex-column flex-sm-row gap-2 mb-3">
                            <div class="d-flex flex-wrap flex-column flex-md-row gap-3">
                                <span class="h2 mb-0 invisible">{{ __('Users') }}</span>
                                <span class="h2 mb-0 invisible">{{ __('Partners') }}</span>
                            </div>
                            <div class="ms-auto">
                                @can('user.broker.update')
                                    <button class="btn btn-primary" onclick="addPartner()">{{ __('Add partner') }}</button>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <x-datatable plugin="user" table="broker_partner"
                                     :filters="['broker_id' => $broker->id]"
                                     :actions="[
                                        'custom_edit' => auth()->user()->can('user.broker.update') ? ['text' => __('Edit'), 'onclick' => 'editBrokerPartner(\':broker_partner\')', 'class' => 'btn-secondary'] : false,
                                     ]"
                        ></x-datatable>
                    </div>
                </div>
            </div>
        </div>
        @can('user.broker.update')
        @can('drive.file.read')
        <div class="tab-pane fade" id="files-tab-pane" role="tabpanel" aria-labelledby="files-tab" tabindex="0">
            <div class="container mb-3">
                <div class="d-flex flex-wrap flex-column flex-sm-row gap-2 mb-3">
                    <span class="h2 mb-0">{{ __('Files') }}</span>
                    @can('drive.file.create')
                        <div class="ms-auto">
                            <button type="button" class="btn btn-primary" onclick="uploadFile()">
                                {{ __('Upload file') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
            <div class="container" x-data="folderContent()" x-init="init()">
                @include('plugin.Drive::file._active_folder', [
                                    'plugin' => 'user', 'subject_type' => 'broker', 'subject_id' => $broker->id, 'breadcrumbs' => false
                                ])
            </div>
        </div>
        @endcan
        @endcan
    </div>
@endsection

@push('scripts-modals')
    @can('user.broker.update')
    <div id="assignUserFormContainer" style="display: none">
        <form>
            {!! renderInputCollection(app(\App\Http\Controllers\Api\HomeApiController::class)->createFormFields('user', 'broker', true, ['only_assign_user' => true])->getOriginalContent(), 'm-1') !!}
        </form>
    </div>
    <div id="addPartnerFormContainer" style="display: none">
        <form>
            {!! renderInputCollection(app(\App\Http\Controllers\Api\HomeApiController::class)->createFormFields('user', 'broker_partner', true, ['only_create' => true, 'broker_id' => $broker->id])->getOriginalContent(), 'm-1') !!}
        </form>
    </div>
    <div id="editPartnerFormContainer" style="display: none">
        <form>
            {!! renderInputCollection(app(\App\Http\Controllers\Api\HomeApiController::class)->createFormFields('user', 'broker_partner', true, ['only_edit' => true])->getOriginalContent(), 'm-1') !!}
        </form>
    </div>
    @endcan
    <div id="commissionTreeFormContainer" style="display: none">
        <form>
            {!! renderInputCollection(app(\App\Http\Controllers\Api\HomeApiController::class)->createFormFields('user', 'broker', true, ['only_commission_tree' => true])->getOriginalContent(), 'm-1') !!}
        </form>
    </div>
@endpush

@push('scripts')
    @include('plugin.User::broker._script_broker_form')
    @can('user.broker.update')
    <script>
        function addPartner() {
            const formContainer = '#addPartnerFormContainer';

            Swal.fire({
                title: '{{ __('Add partner') }}',
                showCancelButton: true,
                confirmButtonText: '{{ __('Save') }}',
                didOpen: () => {
                    $('form', formContainer).appendTo('#swal2-html-container');
                },
                preConfirm: () => {return $('.swal2-container form').valid();},
            }).then(function (result) {
                if (result.isConfirmed) {
                    let data = {};

                    $.each($('[name]:not([name="proengsoft_jsvalidation"])', '.swal2-container'), function (key, value) {
                        data[$(value).attr('name')] = $(value).val();
                    });

                    ajax({
                        url: '{{ route('api.user.broker.partner.assign', ['broker' => $broker->id]) }}',
                        data: data
                    }, (response) => {
                        $.each($.fn.dataTable.tables(), function (key, value) {
                            $(value).DataTable().ajax.reload();
                        });
                        success(response.message)
                    })

                    $('form', '#swal2-html-container').appendTo(formContainer);
                    const selectedOption = $('[name="partner_id"] option:selected', formContainer);
                    if (selectedOption.val() !== '') {
                        selectedOption.remove();
                    }
                } else {
                    $('form', '#swal2-html-container').appendTo(formContainer);
                }

                $('[name="partner_id"]', formContainer).val(null).trigger('change');
                $('[name="external_id"]', formContainer).val('');
            });
        }

        function editBrokerPartner(id) {
            const formContainer = '#editPartnerFormContainer';

            Swal.fire({
                title: '{{ __('Edit') }}',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: '{{ __('Save') }}',
                denyButtonText: '{{ __('Delete') }}',
                didOpen: () => {
                    $('form', formContainer).appendTo('#swal2-html-container');
                },
                preConfirm: () => {return $('.swal2-container form').valid();},
            }).then(function (result) {
                if (result.isConfirmed) {
                    let data = {};

                    $.each($('[name]:not([name="proengsoft_jsvalidation"])', '.swal2-container'), function (key, value) {
                        data[$(value).attr('name')] = $(value).val();
                    });

                    ajax({
                        url: '{{ route('api.user.broker.partner.edit', ['broker' => $broker->id, 'brokerPartner' => ':brokerPartner']) }}'.replace(':brokerPartner', id),
                        data: data
                    }, (response) => {
                        $.each($.fn.dataTable.tables(), function (key, value) {
                            $(value).DataTable().ajax.reload();
                        });
                        success(response.message)
                    })
                } else if (result.isDenied) {
                    ajax({
                        url: '{{ route('api.user.broker.partner.destroy', ['broker' => $broker->id, 'brokerPartner' => ':brokerPartner']) }}'.replace(':brokerPartner', id),
                    }, (response) => {
                        $.each($.fn.dataTable.tables(), function (key, value) {
                            $(value).DataTable().ajax.reload();
                        });
                        success(response.message)
                    })
                }
                $('form', '#swal2-html-container').appendTo(formContainer);
            });
        }

        function assignUser() {
            Swal.fire({
                title: '{{ __('Assign user') }}',
                html: '',
                showCancelButton: true,
                confirmButtonText: '{{ __('Save') }}',
                didOpen: () => {
                    $('form', '#assignUserFormContainer').clone().appendTo('#swal2-html-container');
                },
                preConfirm: () => {return $('.swal2-container form').valid();},
            }).then(function (result) {
                if(result.isConfirmed) {
                    let data = {};

                    $.each($('[name]:not([name="proengsoft_jsvalidation"])', '.swal2-container'), function (key, value) {
                        data[$(value).attr('name')] = $(value).val();
                    });


                    ajax({
                        url: '{{ route('api.user.broker.user.assign', ['broker' => $broker->id]) }}',
                        data: data
                    }, (response) => {
                        $.each($.fn.dataTable.tables(), function (key, value) {
                            $(value).DataTable().ajax.reload();
                        });
                        success(response.message)
                    })
                }
            });
        }

        function unassignUser(user_id) {
            confirm(null, (result) => {
                if (result.isConfirmed) {
                    ajax({
                        url: '{{ route('api.user.broker.user.unassign', ['broker' => $broker->id]) }}',
                        data: {
                            user_id: user_id,
                        },
                    }, (response) => {
                        $.each($.fn.dataTable.tables(), function (key, value) {
                            $(value).DataTable().ajax.reload();
                        });
                        success(response.message)
                    })
                }
            });
        }

        function changeCareerStatus(activate = false) {
            Swal.fire({
                title: '{{ __('Are you sure?') }}',
                icon: "question",
                showConfirmButton: activate,
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: '{{ __('Activate') }}',
                denyButtonText: activate ? '{{ __('Activate without mail') }}' : '{{ __('Deactivate') }}',
                cancelButtonText: '{{ __('Cancel') }}',
                customClass: {
                    confirmButton: 'swal2-confirm btn btn-primary mx-1',
                    denyButton: activate ? 'swal2-confirm btn btn-outline-primary mx-1' : 'swal2-confirm btn btn-danger mx-1',
                    cancelButton: 'swal2-cancel btn btn-outline-secondary mx-1'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    ajax({
                        data: { 'with_mail': true },
                        url: '{{ route('api.user.broker.change-career-status', ['broker' => $broker->id]) }}',
                    }, (response) => {
                        success(response.message, null, null, 2000, () => location.reload());
                    });
                } else if (result.isDenied) {
                    ajax({
                        data: { 'with_mail': false },
                        url: '{{ route('api.user.broker.change-career-status', ['broker' => $broker->id]) }}',
                    }, (response) => {
                        success(response.message, null, null, 2000, () => location.reload());
                    });
                }
            });
        }
    </script>
    @endcan
    <script>
        function showDatePickerModal() {
            const formContainer = '#commissionTreeFormContainer';
            Swal.fire({
                title: '{{ __('forms.select_date') }}',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: '{{ __('Download') }}',
                denyButtonText: '{{ __('Show') }}',
                cancelButtonText: '{{ __('Cancel') }}',
                customClass: {
                    confirmButton: 'swal2-confirm btn btn-info mx-1',
                    denyButton: 'swal2-confirm btn btn-primary mx-1',
                    cancelButton: 'swal2-cancel btn btn-outline-secondary mx-1'
                },
                didOpen: () => {
                    $('form', formContainer).appendTo('#swal2-html-container');
                    {!! JsValidator::formRequest(\App\Plugins\User\app\Http\Requests\BrokerCommissionTreeRequest::class, '.swal2-container form')->view('layouts.jsvalidation') !!};
                },
                preConfirm: () => {
                    return $('.swal2-container form').valid();
                },
            }).then((result) => {
                let data = {};

                $.each($('[name]:not([name="proengsoft_jsvalidation"])', '.swal2-container'), function (key, value) {
                    data[$(value).attr('name')] = $(value).val();
                });

                if (result.isConfirmed) {
                    window.open(
                        '{{ $division
                            ? route('user.division.broker.products.tree', [$division, $broker, 'file'])
                            : route('user.broker.products.tree', [$broker, 'file']) }}' + '?date=' + data.commission_tree_date
                        , '_blank'
                    );
                } else if (result.isDenied) {
                    window.location.href =
                        '{{ $division
                            ? route('user.division.broker.products.tree', [$division, $broker, 'view'])
                            : route('user.broker.products.tree', [$broker, 'view']) }}' + '?date=' + data.commission_tree_date;
                }
                $('form', '#swal2-html-container').appendTo(formContainer);
            });
        }
    </script>
    @can('user.candidate.update')
    <script>
        function sendDocumentationEmail(candidate_id) {
            confirm(null, (result) => {
                if (result.isConfirmed) {
                    ajax({
                        url: '{{ route('api.user.candidate.email.documentation', ['candidate' => ':candidate']) }}'.replace(':candidate', candidate_id),
                    }, (response) => {
                        success(response.message, null, null, 2000)
                    })
                }
            });
        }
    </script>
    @endcan
@endpush
