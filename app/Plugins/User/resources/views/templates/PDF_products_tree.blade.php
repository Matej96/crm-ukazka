@extends('layouts.pdf')

@section('content')

    <div style="text-align: left; margin-bottom: 10px; padding-left: 10px; padding-bottom: 10px; font-weight: bold; border-bottom: 2px solid black; margin-top: -23px">
        <span style="font-size: 16px;">
            {{ __('Broker commission rate') }}
        </span>
    </div>
    <div style="padding: 5px 10px;">
        @if( isset($broker) )
            <span style="display: block; font-size: 12px;">
                <strong>{{ __('Broker') }}:</strong> {{ $broker['complete_name'] }}
            </span>
        @endif

        <span style="display: block; font-size: 12px;">
            <strong>{{ __('Division') }}:</strong> {{ $broker->division->name }}
        </span>
        <div style="width: 50%; float: left">
            <span style="display: block; font-size: 12px;">
                <strong>{{ __('Group') }}:</strong> {{ $group->name }}
            </span>
        </div>
        <div style="width: 50%; float: right; text-align: right">
            <span style="display: block;font-size: 12px;">
               <strong> {{ __('forms.date') }}:</strong> {{ $date }}
            </span>
        </div>
    </div>
    <div style=""></div>
    @if(count($data) > 0)
        @foreach($data as $categoryId => $category)
            <span style="display: block; font-size: 14px; border-bottom: 2px solid #00000080; padding: 5px 10px; margin: 30px 0 25px 0">
                <strong>{{ $category['name'] }}</strong>
            </span>
            @if(isset($category['types']) && count($category['types']) > 0)
                @foreach($category['types'] as $typeId => $type)
                    @if(isset($type['products']) && count($type['products']) > 0)
                            @foreach($type['products'] as $productId => $product)
                                <div style="page-break-inside: avoid;">
                                    <span style="display: block; font-size: 8px;padding: 5px 10px;">
                                         {{ $product['partner'] }}
                                    </span>
                                    <div style="background-color: #f8fafc;  border-bottom: 1px solid #00000080; width: 100%; position: relative;">
                                        <div style="width: 65%; float: left; padding-left: 10px;">
                                            <span style="font-weight: bold; font-size: 12px; ">
                                                {{ $product['name'] }}
                                            </span>
                                        </div>
                                        <div style="width: 35%; float: left; text-align: right;">
                                            <span style="font-size: 12px; line-height: 1; text-align: right; padding-right: 20px;">
                                                @if($product['storno_margin'])
                                                    <span>{{ __('forms.pdf_commission_label') }}</span>
                                                @else
                                                    <span>{{ __('forms.pdf_commission_without_storno_label') }}</span>
                                                @endif
                                            </span>
                                        </div>
                                        <div style="clear: both;"></div>
                                    </div>

                                    <div style="width: 100%; position: relative; margin-bottom: 20px;">
                                        <div style="width: 65%; float: left; border-bottom: 1px solid #00000020">
                                            <div style="margin-top: -8px;">
                                                <span style="font-size: 10px; padding-left: 10px;">
                                                    {{ __('forms.initial_commission') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div style="width: 35%; float: left; text-align: right; border-bottom: 1px solid #00000020">
                                            <div style="margin-top: -8px">
                                                <span style="font-size: 10px; padding-right: 10px;">
                                                    {{ number_format($product['broker_initial_commission'], 2) }} @if($broker->division->type == 'percentage') % @else BK @endif

                                                    @if($product['storno_margin'])
                                                        ( {{ number_format($product['broker_initial_commission_storno'], 2) }} @if($broker->division->type == 'percentage') % @else BK @endif)
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    @if(isset($product['broker_follow_up_commission']))
                                        <div style="width: 100%; position: relative; margin-bottom: 40px;">
                                            <div style="width: 75%; float: left; border-bottom: 1px solid #00000020">
                                                <div style="margin-top: -8px;">
                                                    <span style="font-size: 10px; padding-left: 10px;">
                                                        {{ __('forms.follow_up_commission') }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div style="width: 25%; float: left; text-align: right; border-bottom: 1px solid #00000020">
                                                <div style="margin-top: -8px">
                                                    <span style="font-size: 10px; padding-right: 10px;">
                                                        {{ number_format($product['broker_follow_up_commission'], 2) }} @if($broker->division->type == 'percentage') % @else BK @endif

                                                        @if($product['storno_margin'])
                                                            ( {{ number_format($product['broker_follow_up_commission_storno'], 2) }} @if($broker->division->type == 'percentage') % @else BK @endif)
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                    @else
                        <p>{{ __('No product products') }}</p>
                    @endif
                @endforeach
            @else
                <p>{{ __('No product types') }}</p>
            @endif
        @endforeach
    @else
        <p>{{ __('No product categories') }}</p>
    @endif

@endsection

