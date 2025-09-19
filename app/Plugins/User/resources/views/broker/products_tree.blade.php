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
                        route('user.broker.show', [$broker]) . '#brokers-tab' => \App\Plugins\User\app\Models\Broker::getBreadcrumbClassName(\App\Plugins\User\app\Models\Broker::class),
                    ])" :active="__('Broker commission rate')"
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
        <div class="d-flex flex-wrap flex-column flex-sm-row gap-2 mb-3">
            <span class="h2 mb-0">{{ __('Broker commission rate') }}</span>
            <div class="ms-auto">
                <a href="{{ $division ? route('user.division.broker.show', [$division, $broker]) : route('user.broker.show', $broker) }}"
                   class="btn btn-outline-secondary">{{__('Back')}}</a>
            </div>
        </div>
        <div class="mb-3">
            @if($broker->division->type == 'percentage')
                <span class="badge text-bg-primary px-3">{{ __('forms.initial_commission_label') }}</span>
                <span class="badge text-bg-secondary px-3">{{ __('forms.follow_up_commission_label') }}</span>
            @else
                <span class="badge text-bg-primary px-3">{{ __('forms.point_initial_commission_label') }}</span>
                <span class="badge text-bg-secondary px-3">{{ __('forms.point_follow_up_commission_label') }}</span>
            @endif
        </div>
        @if(count($data) > 0)
            <div class="accordion border-top" id="accordionCategories">
            @foreach($data as $categoryId => $category)
                <h2 class="accordion-header" id="headingCategory{{ $categoryId }}">
                    <button class="accordion-button bg-transparent text-body" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseCategory{{ $categoryId }}" aria-expanded="true"
                            aria-controls="collapseCategory{{ $categoryId }}">
                        <span class="h4 mb-0">{{ $category['name'] }}</span>

{{--                                        <span class="badge {{ $category->status_class }} ms-2">{{ $category->status }}</span>--}}
{{--                                        <span class="badge bg-secondary ms-2">{{ $category->commission }}</span>--}}
                    </button>
                </h2>
                <div id="collapseCategory{{ $categoryId }}" class="accordion-collapse collapse show" aria-labelledby="headingCategory{{ $categoryId }}">
                    <div class="accordion-body p-0 ps-4">
                        @if(isset($category['types']) && count($category['types']) > 0)
                            <div class="accordion" id="accordionTypes{{ $categoryId }}">
                                @foreach($category['types'] as $typeId => $type)
                                    <div class="accordion-item border-0">
                                        <h2 class="accordion-header" id="headingType{{ $typeId }}">
                                            <button class="accordion-button bg-transparent text-body" type="button" data-bs-toggle="collapse" data-bs-target="#collapseType{{ $typeId }}" aria-expanded="false" aria-controls="collapseType{{ $typeId }}">
                                                <span class="h6 mb-0">{{ $type['name'] }}</span>

{{--                                                                <span class="badge {{ $type->status_class }} ms-2">{{ $type->status }}</span>--}}
{{--                                                                <span class="badge bg-secondary ms-2">{{ $type->commission }}</span>--}}
                                            </button>
                                        </h2>
                                        <div id="collapseType{{ $typeId }}" class="accordion-collapse collapse show" aria-labelledby="headingType{{ $typeId }}">
                                            <div class="accordion-body p-0 ps-4">
                                                @if(isset($type['products']) && count($type['products']) > 0)
                                                    <ul class="list-group list-group-flush">
                                                        @foreach($type['products'] as $productId => $product)
                                                            <li class="list-group-item bg-transparent">
                                                                {{ $product['name'] }}
                                                                <span class="badge text-bg-primary ms-2">{{ round($product['broker_initial_commission'], 2) }} @if($broker->division->type == 'percentage') % @else BK @endif
                                                                    @if($product['storno_margin'])
                                                                        ( {{ round($product['broker_initial_commission_storno'], 2) }}
                                                                        @if($broker->division->type == 'percentage')
                                                                            {{ '%' }}
                                                                        @else
                                                                            {{ 'BK' }}
                                                                        @endif)
                                                                    @endif
                                                                </span>
                                                                @if(isset($product['broker_follow_up_commission']))
                                                                    <span class="badge text-bg-secondary ms-2">{{ round($product['broker_follow_up_commission'], 2) }} @if($broker->division->type == 'percentage') % @else BK @endif
                                                                        @if($product['storno_margin'])
                                                                            ( {{ round($product['broker_follow_up_commission_storno'], 2) }}
                                                                            @if($broker->division->type == 'percentage')
                                                                                {{ '%' }}
                                                                            @else
                                                                                {{ 'BK' }}
                                                                            @endif)
                                                                        @endif
                                                                    </span>
                                                                @endif
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <div class="alert alert-warning py-1 m-2">{{ __('No products') }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-warning py-1 m-2">{{ __('No product types') }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
            </div>
        @else
            <div class="alert alert-warning py-1 m-2">{{ __('No product categories') }}</div>
        @endif
    </div>
@endsection

