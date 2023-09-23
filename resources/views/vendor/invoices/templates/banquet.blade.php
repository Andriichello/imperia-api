<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ $invoice->name }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <style type="text/css" media="screen">
        html {
            font-family: sans-serif;
            line-height: 1.15;
            margin: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            text-align: left;
            background-color: #fff;
            font-size: 12px;
            margin: 36px;
        }

        h4 {
            margin-top: 0;
            margin-bottom: 0.5rem;
        }

        p {
            margin-top: 0;
            margin-bottom: 1rem;
        }

        strong {
            font-weight: bolder;
        }

        img {
            vertical-align: middle;
            border-style: none;
        }

        table {
            border-collapse: collapse;
        }

        th {
            text-align: inherit;
        }

        h4, .h4 {
            margin-bottom: 0.5rem;
            font-weight: 500;
            line-height: 1.2;
        }

        h4, .h4 {
            font-size: 1.5rem;
        }

        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            vertical-align: top;
        }

        .table.table-items td {
            border-top: 1px solid #dee2e6;
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
        }

        .p-0 {
            padding: 0;
        }

        .pr-0,
        .px-0 {
            padding-right: 0 !important;
        }

        .pl-0,
        .px-0 {
            padding-left: 0 !important;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .text-uppercase {
            text-transform: uppercase !important;
        }
        * {
            font-family: "DejaVu Sans";
        }
        body, h1, h2, h3, h4, h5, h6, table, th, tr, td, p, div {
            line-height: 1.1;
        }
        .party-header {
            font-size: 1.5rem;
            font-weight: 400;
        }
        .total-amount {
            font-size: 16px;
            font-weight: 700;
        }
        .border-0 {
            border: none !important;
        }
        .cool-gray {
            color: #6B7280;
        }

        .font-light {
            font-weight: lighter;
        }
        .font-regular {
            font-weight: normal;
        }
        .font-medium {
            font-weight: bolder;
        }
        .font-bold {
            font-weight: bold;
        }

        .date {
            font-size: 14px;
        }
        .time {
            font-size: 14px;
        }
        .items-title {
            font-size: 16px;
        }

        .seller-name {
            font-size: 16px;
        }
        .buyer-name {
            font-size: 16px;
        }

        .min-w-20 {
            min-width: 80px;
        }
    </style>
</head>

<body>
@php
    $onlyMenus = $invoice->onlyMenus();
    $onlySections = $invoice->onlySections();

    $menus = $invoice->getMenus() ?? [];
    $spaces = $invoice->getSpaces() ?? [];
    $tickets = $invoice->getTickets() ?? [];
    $services = $invoice->getServices() ?? [];

    $comments = $invoice->getComments() ?? [];
    $productsByMenus = $invoice->getProductsByMenus() ?? [];
@endphp

{{-- Header --}}
@if($invoice->logo)
    <img src="{{ $invoice->getLogo() }}" alt="logo" height="100">
@endif

<table class="table">
    <tbody>
    <tr>
        <td class="border-0 p-0" width="70%">
            <h4 class="text-uppercase">
                <strong>{{ $invoice->name }}</strong>
            </h4>
        </td>
        @if(!empty($invoice->getDate()) && !empty($invoice->getStartTime()))
            <td class="border-0 p-0">
                <div>
                <span class="date">
                    {{ __('invoices::invoice.date') }}: <strong>{{ $invoice->getDate() }}</strong>
                </span>
                </div>
                <div>
                <span class="time">
                    {{ __('invoices::invoice.time') }}: <strong>{{ $invoice->getStartTime() }}</strong> - <strong>{{ $invoice->getEndTime() }}</strong>
                </span>
                </div>
            </td>
        @endif
    </tr>
    </tbody>
</table>

@if(($onlySections === null || in_array('info', $onlySections ?? [])))
    {{-- Seller - Buyer --}}
    <table class="table">
        <tbody>
        <tr>
            <td class="p-0">
                @if($invoice->seller->name)
                    <p class="seller-name">
                        <strong>{{ $invoice->seller->name }}</strong>
                    </p>
                @endif

                @if($invoice->seller->address)
                    <p class="seller-address">
                        {{ __('invoices::invoice.address') }}: <span class="font-bold">{{ $invoice->seller->address }}</span>
                    </p>
                @endif

                @if($invoice->seller->phone)
                    <p class="seller-phone">
                        {{ __('invoices::invoice.phone') }}: <span class="font-bold">{{ $invoice->seller->phone }}</span>
                    </p>
                @endif

                @foreach($invoice->seller->custom_fields as $key => $value)
                    @if(!empty($value))
                        <p class="seller-custom-field">
                            {{ translate("invoices::invoice.$key", [], ucfirst($key)) }}: <span class="font-bold">{{ $value }}</span>
                        </p>
                    @endif
                @endforeach
            </td>
            <td class="border-0"></td>
            <td class="p-0">
                @if($invoice->buyer->name)
                    <p class="buyer-name">
                        <strong>{{ $invoice->buyer->name }}</strong>
                    </p>
                @endif

                @if($invoice->buyer->address)
                    <p class="buyer-address">
                        {{ __('invoices::invoice.address') }}: <span class="font-bold">{{ $invoice->buyer->address }}</span>
                    </p>
                @endif

                @if($invoice->buyer->phone)
                    <p class="buyer-phone">
                        {{ __('invoices::invoice.phone') }}: <span class="font-bold">{{ $invoice->buyer->phone }}</span>
                    </p>
                @endif

                @foreach($invoice->buyer->custom_fields as $key => $value)
                    @if(!empty($value))
                        <p class="buyer-custom-field">
                            {{ translate("invoices::invoice.$key", [], ucfirst($key)) }}: <span class="font-bold">{{ $value }}</span>
                        </p>
                    @endif
                @endforeach
            </td>
        </tr>
        </tbody>
    </table>
@endif

{{-- Table --}}

@if(!empty($comments) && ($onlySections === null || in_array('comments', $onlySections ?? [])))
    <table class="table table-items">
        <thead>
        <tr>
            <th scope="col" class="border-0 p-0 items-title">{{ __('invoices::invoice.comments') }}</th>
        </tr>
        </thead>
        <tbody>

        {{-- Items --}}
        @foreach($comments as $comment)
            <tr>
                <td class="p-0">
                    {{ $comment['text'] }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

@if(!empty($tickets) && ($onlySections === null || in_array('tickets', $onlySections ?? [])))
    <table class="table table-items">
        <thead>
        <tr>
            <th scope="col" class="border-0 p-0 items-title">{{ __('invoices::invoice.tickets') }}</th>
            <th scope="col" class="text-right border-0 p-0 min-w-20">{{ __('invoices::invoice.price') }}</th>
            <th scope="col" class="text-right border-0 p-0 min-w-20">{{ __('invoices::invoice.sum') }}</th>
        </tr>
        </thead>
        <tbody>

        {{-- Items --}}
        @foreach($tickets as $item)
            <tr>
                <td class="p-0">
                    {{ $item->title}}

                    @if($item->description)
                        <br><span class="cool-gray">{{ $item->description }}</span>
                    @endif
                    @foreach($item->getComments() as $comment)
                        <br><span class="cool-gray">{{ $comment['text'] }}</span>
                    @endforeach
                </td>
                <td class="text-right">
                    {{ $invoice->itemFormattedPrice($item) }}
                </td>
                <td class="text-right p-0">
                    {{ $invoice->formatCurrency($item->sub_total_price) }}
                </td>
            </tr>
        @endforeach
            {{-- Summary --}}
            <tr>
                <td colspan="1" class="border-0"></td>
                <td class="text-right p-0">Total</td>
                <td class="text-right p-0 total-amount">
                    {{ $invoice->formatCurrency($invoice->getTotal($tickets)) }}
                </td>
            </tr>
        </tbody>
    </table>
@elseif(($onlySections === null || in_array('tickets', $onlySections ?? [])))
    <table class="table table-items">
        <thead>
        <tr>
            <th scope="col" class="border-0 p-0 items-title">{{ __('invoices::invoice.tickets') }}</th>
            <th scope="col" class="text-right border-0 p-0">{{ __('invoices::invoice.quantity') }}</th>
            <th scope="col" class="text-right border-0 p-0 min-w-20">{{ __('invoices::invoice.price') }}</th>
            <th scope="col" class="text-right border-0 p-0 min-w-20">{{ __('invoices::invoice.sum') }}</th>
        </tr>
        </thead>
        <tbody>

        {{-- Items --}}
        <tr>
            <td class="p-0">{{ __('invoices::invoice.children') }}</td>
            <td class="text-right">{{ ($amount = $invoice->getChildrenAmount()) ?? '' }}</td>
            <td class="text-right">{{ ($price = $invoice->getChildTicketPrice()) ? $invoice->formatCurrency($price) : '' }}</td>
            <td class="text-right p-0">{{ $price && $amount ? ($childTicketsTotal = $invoice->formatCurrency($price * $amount)) : '' }}</td>
        </tr>
        <tr>
            <td class="p-0">{{ __('invoices::invoice.adults') }}</td>
            <td class="text-right">{{ ($amount = $invoice->getAdultsAmount()) ?? '' }}</td>
            <td class="text-right">{{ ($price = $invoice->getAdultTicketPrice()) ? $invoice->formatCurrency($price) : '' }}</td>
            <td class="text-right p-0">{{ $price && $amount ? ($adultTicketsTotal = $invoice->formatCurrency($price * $amount)) : '' }}</td>
        </tr>
        {{-- Summary --}}
        <tr>
            <td colspan="1" class="border-0"></td>
            <td class="text-right p-0"></td>
            <td class="text-right p-0">{{ __('invoices::invoice.total') }}</td>
            <td colspan="1" class="text-right p-0 total-amount min-w-20">{{ (isset($childTicketsTotal) || isset($adultTicketsTotal)) ? $invoice->formatCurrency((float)($childTicketsTotal ?? 0) + (float)($adultTicketsTotal ?? 0)) : '' }}</td>
        </tr>
        </tbody>
    </table>
@endif

@if(!empty($spaces) && ($onlySections === null || in_array('spaces', $onlySections ?? [])))
    <table class="table table-items">
        <thead>
        <tr>
            <th scope="col" class="border-0 p-0 items-title">{{ __('invoices::invoice.spaces') }}</th>
            <th scope="col" class="border-0 p-0">{{ __('invoices::invoice.duration') }}</th>
            <th scope="col" class="text-right border-0 p-0 min-w-20">{{ __('invoices::invoice.price') }}</th>
            <th scope="col" class="text-right border-0 p-0 min-w-20">{{ __('invoices::invoice.sub_total') }}</th>
        </tr>
        </thead>
        <tbody>

        {{-- Items --}}
        @foreach($spaces as $item)
            <tr>
                <td class="p-0">
                    {{ $item->title}}

                    @if($item->description)
                        <br><span class="cool-gray">{{ $item->description }}</span>
                    @endif
                    @foreach($item->getComments() as $comment)
                        <br><span class="cool-gray">{{ $comment['text'] }}</span>
                    @endforeach
                </td>
                <td>
                    {{ $item->getDuration() }}
                </td>
                <td class="text-right">
                    {{ $invoice->itemFormattedPrice($item) }}
                </td>
                <td class="text-right p-0">
                    {{ $invoice->formatCurrency($item->sub_total_price) }}
                </td>
            </tr>
        @endforeach
            {{-- Summary --}}
            <tr>
                <td colspan="2" class="border-0"></td>
                <td class="text-right p-0">Total</td>
                <td class="text-right p-0 total-amount">
                    {{ $invoice->formatCurrency($invoice->getTotal($spaces)) }}
                </td>
            </tr>
        </tbody>
    </table>
@endif

@if(!empty($services) && ($onlySections === null || in_array('services', $onlySections ?? [])))
    <table class="table table-items">
        <thead>
        <tr>
            <th scope="col" class="border-0 p-0 items-title">{{ __('invoices::invoice.services') }}</th>
            <th scope="col" class="border-0 p-0">{{ __('invoices::invoice.duration') }}</th>
            <th scope="col" class="text-right border-0 p-0 min-w-20">{{ __('invoices::invoice.price') }}</th>
            <th scope="col" class="text-right border-0 p-0 min-w-20">{{ __('invoices::invoice.sum') }}</th>
        </tr>
        </thead>
        <tbody>

        {{-- Items --}}
        @foreach($services as $item)
            <tr>
                <td class="p-0">
                    {{ $item->title}}

                    @if($item->description)
                        <br><span class="cool-gray">{{ $item->description }}</span>
                    @endif
                    @foreach($item->getComments() as $comment)
                        <br><span class="cool-gray">{{ $comment['text'] }}</span>
                    @endforeach
                </td>
                <td>
                    {{ $item->getDuration() }}
                </td>
                <td class="text-right">
                    {{ $invoice->itemFormattedPrice($item) }}
                </td>
                <td class="text-right p-0">
                    {{ $invoice->formatCurrency($item->sub_total_price) }}
                </td>
            </tr>
        @endforeach
            {{-- Summary --}}
            <tr>
                <td colspan="2" class="border-0"></td>
                <td class="text-right p-0">Total</td>
                <td class="text-right p-0 total-amount">
                    {{ $invoice->formatCurrency($invoice->getTotal($services)) }}
                </td>
            </tr>
        </tbody>
    </table>
@endif

@if(!empty($menus) && ($onlySections === null || in_array('menus', $onlySections ?? [])))
    @foreach($menus as $menu)
        @if($onlyMenus === null || in_array(data_get($menu, 'id'), $onlyMenus ?? []))
            <table class="table table-items">
                <thead>
                <tr>
                    <th scope="col" class="border-0 p-0 items-title">{{ data_get($menu, 'title') }}</th>
                    <th scope="col" class="text-center border-0 p-0">{{ __('invoices::invoice.variant') }}</th>
                    <th scope="col" class="text-center border-0 p-0">{{ __('invoices::invoice.quantity') }}</th>
                    <th scope="col" class="text-right border-0 p-0 min-w-20">{{ __('invoices::invoice.price') }}</th>
                    <th scope="col" class="text-right border-0 p-0 min-w-20">{{ __('invoices::invoice.sum') }}</th>
                </tr>
                </thead>
                <tbody>

                {{-- Items --}}
                @foreach($products = $productsByMenus[data_get($menu, 'id')] as $item)
                    <tr>
                        <td class="p-0">
                            {{ $item->title}}

                            @if($item->description)
                                <br><span class="cool-gray">{{ $item->description }}</span>
                            @endif
                            @foreach($item->getComments() as $comment)
                                <br><span class="cool-gray">{{ $comment['text'] }}</span>
                            @endforeach
                        </td>

                        <td class="text-center">{{ $item->getVariant() }}</td>

                        <td class="text-center">{{ $item->quantity }}</td>

                        <td class="text-right">
                            {{ $invoice->itemFormattedPrice($item) }}
                        </td>

                        @if($invoice->hasItemDiscount)
                            <td class="text-right">
                                {{ $invoice->formatCurrency($item->discount) }}
                            </td>
                        @endif
                        @if($invoice->hasItemTax)
                            <td class="text-right">
                                {{ $invoice->formatCurrency($item->tax) }}
                            </td>
                        @endif

                        <td class="text-right p-0">
                            {{ $invoice->formatCurrency($item->sub_total_price) }}
                        </td>
                    </tr>
                @endforeach

                {{-- Summary --}}
                <tr>
                    <td colspan="1" class="border-0"></td>
                    <td class="text-right p-0"></td>
                    <td class="text-right p-0">{{ __('invoices::invoice.total') }}</td>
                    <td colspan="2" class="text-right p-0 total-amount min-w-20">
                        {{ $invoice->formatCurrency($invoice->getTotal($products)) }}
                    </td>
                </tr>
                </tbody>
            </table>
        @endif
    @endforeach
@endif

@if($invoice->notes)
    <p>
        {{ trans('invoices::invoice.notes') }}: {!! $invoice->notes !!}
    </p>
@endif

<script type="text/php">
    if (isset($pdf) && $PAGE_COUNT > 1) {
        $text = __('invoices::invoice.page') . " {PAGE_NUM} / {PAGE_COUNT}";
        $size = 10;
        $font = $fontMetrics->getFont("Verdana");
        $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
        $x = ($pdf->get_width() - $width);
        $y = $pdf->get_height() - 35;
        $pdf->page_text($x, $y, $text, $font, $size);
    }
</script>
</body>
</html>
