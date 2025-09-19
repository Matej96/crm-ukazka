@extends('layouts.app')

@section('breadcrumb')
    <x-breadcrumb :active="\App\Plugins\User\app\Models\Broker::getBreadcrumbClassName(\App\Plugins\User\app\Models\Broker::class)"/>
@endsection

@section('content')
    <div class="container">
        <ul class="nav flex-column flex-md-row gap-3 position-absolute">
            <li class="nav-item" role="presentation">
                <button class="nav-link p-0 active" data-bs-toggle="tab" data-bs-target="#brokers-brokers-tab-pane" type="button" role="tab" aria-controls="brokers-brokers-tab-pane" aria-selected="true">
                    <span class="h2 mb-0">{{ __('Brokers') }}</span>
                </button>
            </li>
            @can('user.candidate.read')
                <li class="nav-item" role="presentation">
                    <button class="nav-link p-0" data-bs-toggle="tab" data-bs-target="#brokers-candidates-tab-pane" type="button" role="tab" aria-controls="brokers-candidates-tab-pane" aria-selected="false">
                        <span class="h2 mb-0">{{ __('Candidates') }}</span>
                    </button>
                </li>
            @endcan
        </ul>
    </div>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="brokers-brokers-tab-pane" role="tabpanel" aria-labelledby="brokers-brokers-tab" tabindex="0">
            <div class="container">
                <div class="d-flex flex-wrap flex-column flex-sm-row gap-2 mb-3">
                    <div class="d-flex flex-wrap flex-column flex-md-row gap-3">
                        <span class="h2 mb-0 invisible">{{ __('Brokers') }}</span>
                        @can('user.candidate.read')
                            <span class="h2 mb-0 invisible">{{ __('Candidates') }}</span>
                        @endcan
                    </div>
                    <div class="ms-auto">
                        @can('user.broker.create')
                            <a href="{{ route('user.broker.create') }}" class="btn btn-primary btn-wider-custom">{{ __('Create') }}</a>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="col-md-12">
                    <x-datatable plugin="user" table="broker"></x-datatable>
                </div>
            </div>
        </div>
        @can('user.candidate.read')
        <div class="tab-pane fade" id="brokers-candidates-tab-pane" role="tabpanel" aria-labelledby="brokers-candidates-tab" tabindex="0">
            <div class="container">
                <div class="d-flex flex-wrap flex-column flex-sm-row gap-2 mb-3">
                    <div class="d-flex flex-wrap flex-column flex-md-row gap-3">
                        <span class="h2 mb-0 invisible">{{ __('Brokers') }}</span>
                        @can('user.candidate.read')
                            <span class="h2 mb-0 invisible">{{ __('Candidates') }}</span>
                        @endcan
                    </div>
                    <ul class="nav gap-3 align-self-center ms-2" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link p-0 active" data-bs-toggle="tab" data-bs-target="#candidates-ongoing-tab-pane" type="button" role="tab" aria-controls="candidates-ongoing-tab-pane" aria-selected="true">
                                <span class="h5 mb-0">{{ __('Ongoing') }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link p-0" data-bs-toggle="tab" data-bs-target="#candidates-created-tab-pane" type="button" role="tab" aria-controls="candidates-created-tab-pane" aria-selected="true">
                                <span class="h5 mb-0">{{ __('Created') }}</span>
                            </button>
                        </li>
                    </ul>
                    <div class="ms-auto">
                        @can('user.candidate.create')
                            <a href="{{ route('user.candidate.create') }}" class="btn btn-primary">{{ __('Create candidate') }}</a>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="candidates-ongoing-tab-pane" role="tabpanel" tabindex="0">
                        <x-datatable plugin="user" table="candidate"
                                     :exceptColumns="['broker_id']"
                        ></x-datatable>
                    </div>
                    <div class="tab-pane fade" id="candidates-created-tab-pane" role="tabpanel" tabindex="0">
                        <x-datatable plugin="user" table="candidate"
                                     :exceptColumns="['status']"
                                     :filters="[
                                        'status' => 'created',
                                     ]"
                                     :actions="[
                                        'custom_show' => auth()->user()->can('user.broker.read') ? ['text' => __('Detail'), 'class' => 'btn-info', 'href' => route('user.broker.show', ':_broker_id') . '#detail-tab', 'replace' => ['_broker_id']] : false,
                                     ]"
                        ></x-datatable>
                    </div>
                </div>
            </div>
        </div>
        @endcan
    </div>
@endsection

