@php
    $formFields = \App\Plugins\User\app\Models\Broker::splitBrokerInputCollection($formFields, $dividedFields);
    $formFieldsA = ['educational_attainment', 'iban'];
    $formFields = splitInputCollection($formFields, $formFieldsA);
@endphp
@if(($candidate_fields = collect($dividedFields['candidate_fields'] ?? []))->isNotEmpty())
    <div class="row">
        {!! renderInputCollection($candidate_fields) !!}
    </div>
@endif
@if(($broker_fields = collect($dividedFields['broker_fields'] ?? []))->isNotEmpty())
    @php
        $broker_fieldsA = ['division_id', 'broker_type'];
        $broker_fields = splitInputCollection($broker_fields, $broker_fieldsA);
    @endphp
    <div class="row">
        {!! renderInputCollection($broker_fieldsA) !!}
    </div>
    <div class="row">
        {!! renderInputCollection($broker_fields) !!}
    </div>
@endif
<div class="h4 my-3">{{ __('Basic data') }}</div>
@if(($personal_fields = collect($dividedFields['personal_fields'] ?? []))->isNotEmpty())
    <div class="row">
        {!! renderInputCollection($personal_fields) !!}
    </div>
@endif
@if(($contact_fields = collect($dividedFields['contact_fields'] ?? []))->isNotEmpty())
    <div class="row">
        {!! renderInputCollection($contact_fields) !!}
    </div>
@endif
<div class="row">
    @if(($permanent_address_fields = collect($dividedFields['permanent_address_fields'] ?? []))->isNotEmpty())
        <div class="col-md-12 permanent_address_container">
            <div class="row">
                <h6>{{ __('forms.permanent_address_id') }}</h6>
                {!! renderInputCollection($permanent_address_fields) !!}
            </div>
        </div>
    @endif
    @if(($temporary_address_fields = collect($dividedFields['temporary_address_fields'] ?? []))->isNotEmpty())
        <div class="col-md-12 temporary_address_container">
            <div class="row">
                {!! renderInputCollection($temporary_address_fields) !!}
            </div>
        </div>
    @endif
    @if(($business_address_fields = collect($dividedFields['business_address_fields'] ?? []))->isNotEmpty())
        <div class="col-md-12 business_address_container">
            <div class="row">
                <h6>{{ __('forms.business_address_id') }}</h6>
                {!! renderInputCollection($business_address_fields) !!}
            </div>
        </div>
    @endif
    @if(($shipping_address_fields = collect($dividedFields['shipping_address_fields'] ?? []))->isNotEmpty())
        <div class="col-md-12 shipping_address_container">
            <div class="row">
                {!! renderInputCollection($shipping_address_fields) !!}
            </div>
        </div>
    @endif
</div>
{{--identity_card--}}
@if(($identity_fields = collect($dividedFields['identity_fields'] ?? []))->isNotEmpty())
    <div class="row">
        {!! renderInputCollection($identity_fields) !!}
    </div>
@endif
@if(($representative_fields = collect($dividedFields['representative_fields'] ?? []))->isNotEmpty())
<div id="representatives_container" class="mb-3">
    <h6>{{ __('Representatives') }}</h6>
    <div class="d-flex flex-wrap column-gap-3 row-gap-1">
        <div class="modal fade" id="representativeFormModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
             data-user-birth-autocomplete="true"
        >
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-xl-down">
                <div class="modal-content">
                    <div class="modal-header justify-content-center">
                        <h5 class="modal-title display-6">{{ __('Representative') }}</h5>
                    </div>
                    <div class="modal-body">
                        @php
                            $representative_fields = \App\Plugins\User\app\Models\Broker::splitBrokerInputCollection($representative_fields, $repr_divided_fields, ['is_representative' => true]);
                        @endphp
                        <div class="row">
                            @if(($repr_broker_fields = collect($repr_divided_fields['broker_fields'] ?? []))->isNotEmpty())
                                {!! renderInputCollection($repr_broker_fields) !!}
                            @endif
                            @if(($repr_representative_fields = collect($repr_divided_fields['representative_fields'] ?? []))->isNotEmpty())
                                {!! renderInputCollection($repr_representative_fields) !!}
                            @endif
                            @if(($repr_personal_fields = collect($repr_divided_fields['personal_fields'] ?? []))->isNotEmpty())
                                {!! renderInputCollection($repr_personal_fields) !!}
                            @endif
                            @if(($repr_contact_fields = collect($repr_divided_fields['contact_fields'] ?? []))->isNotEmpty())
                                {!! renderInputCollection($repr_contact_fields) !!}
                            @endif
                            @if(($repr_permanent_address_fields = collect($repr_divided_fields['permanent_address_fields'] ?? []))->isNotEmpty())
                                <div class="col-md-12 permanent_address_container">
                                    <div class="row">
                                        <h6>{{ __('forms.permanent_address_id') }}</h6>
                                        {!! renderInputCollection($repr_permanent_address_fields) !!}
                                    </div>
                                </div>
                            @endif
                            @if(($repr_temporary_address_fields = collect($repr_divided_fields['temporary_address_fields'] ?? []))->isNotEmpty())
                                <div class="col-md-12 temporary_address_container">
                                    <div class="row">
                                        {!! renderInputCollection($repr_temporary_address_fields) !!}
                                    </div>
                                </div>
                            @endif
                            @if(($repr_shipping_address_fields = collect($repr_divided_fields['shipping_address_fields'] ?? []))->isNotEmpty())
                                <div class="col-md-12 shipping_address_container">
                                    <div class="row">
                                        {!! renderInputCollection($repr_shipping_address_fields) !!}
                                    </div>
                                </div>
                            @endif
                            @if(($repr_identity_fields = collect($repr_divided_fields['identity_fields'] ?? []))->isNotEmpty())
                                {!! renderInputCollection($repr_identity_fields) !!}
                            @endif
                            {!! renderInputCollection($representative_fields) !!}
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                data-bs-target="#representativeFormModal">{{ __('Add representative') }}</button>
        <div id="representatives" class="d-flex flex-wrap gap-1"></div>
    </div>
</div>
@endif
@if(($broker_info_fields = collect($dividedFields['broker_info_fields'] ?? []))->isNotEmpty())
    <div class="h4 my-3">{{ __('Additional data') }}</div>
    @php
        $broker_info_fieldsB = ['activity_region_ids', 'previous_sfa'];
        $broker_info_fieldsC = ['career_start_at', 'career_start_type'];
        $broker_info_fieldsD = ['contract_start_at', 'trust_signed_at', 'criminal_listed_at'];
        $broker_info_fieldsE = ['career_exit_at', 'career_exit_type', 'career_exit_note'];
        $broker_info_fields = splitInputCollection($broker_info_fields, $broker_info_fieldsB, $broker_info_fieldsC, $broker_info_fieldsD, $broker_info_fieldsE);
    @endphp
    <div class="row">
        {!! renderInputCollection($broker_info_fieldsB) !!}
    </div>
    <div class="row">
        {!! renderInputCollection($broker_info_fieldsC) !!}
    </div>
    <div class="row">
        {!! renderInputCollection($broker_info_fieldsD) !!}
    </div>
    <div class="row">
        {!! renderInputCollection($broker_info_fieldsE) !!}
    </div>
    <div class="row">
        {!! renderInputCollection($broker_info_fields) !!}
    </div>
@endif
<div class="row">
    {!! renderInputCollection($formFieldsA) !!}
</div>
@if(($broker_nbs_fields = collect($dividedFields['broker_nbs_fields'] ?? []))->isNotEmpty())
    <div class="row">
        {!! renderInputCollection($broker_nbs_fields) !!}
    </div>
@endif
@if(($broker_nbs_sector_fields = collect($dividedFields['broker_nbs_sector_fields'] ?? []))->isNotEmpty())
    <div class="h4 my-3">{{ __('Registrácia v sektoroch') }}</div>
    <div class="row">
        {!! renderInputCollection($broker_nbs_sector_fields) !!}
    </div>
@endif
@if(($other_fields = collect($dividedFields['other_fields'] ?? []))->isNotEmpty())
    <div class="row">
        {!! renderInputCollection($other_fields) !!}
    </div>
@endif
<div class="row">
    {!! renderInputCollection($formFields) !!}
</div>
