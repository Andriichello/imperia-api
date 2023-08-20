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
            font-size: 14px;
        }

        .seller-name {
            font-size: 16px;
        }
        .buyer-name {
            font-size: 16px;
        }
    </style>
</head>

<body>
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
        <td class="border-0 p-0">
            <div>
                <span class="date">
                    Date: <strong>{{ $invoice->getDate() }}</strong>
                </span>
            </div>
            <div>
                <span class="time">
                    Time: <strong>{{ $invoice->getStartTime() }}</strong> - <strong>{{ $invoice->getEndTime() }}</strong>
                </span>
            </div>
        </td>
    </tr>
    </tbody>
</table>

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
                    Address: <span class="font-bold">{{ $invoice->seller->address }}</span>
                </p>
            @endif

            @if($invoice->seller->phone)
                <p class="seller-phone">
                    Phone: <span class="font-bold">{{ $invoice->seller->phone }}</span>
                </p>
            @endif

            @foreach($invoice->seller->custom_fields as $key => $value)
                <p class="seller-custom-field">
                    {{ ucfirst($key) }}: <span class="font-bold">{{ $value }}</span>
                </p>
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
                    Address: <span class="font-bold">{{ $invoice->buyer->address }}</span>
                </p>
            @endif

            @if($invoice->buyer->phone)
                <p class="buyer-phone">
                    Phone: <span class="font-bold">{{ $invoice->buyer->phone }}</span>
                </p>
            @endif

            @foreach($invoice->buyer->custom_fields as $key => $value)
                <p class="buyer-custom-field">
                    {{ ucfirst($key) }}: <span class="font-bold">{{ $value }}</span>
                </p>
                @endforeach
        </td>
    </tr>
    </tbody>
</table>

@php
    $spaces = $invoice->getSpaces() ?? [];
    $tickets = $invoice->getTickets() ?? [];
    $services = $invoice->getServices() ?? [];
    $products = $invoice->getProducts() ?? [];
@endphp
{{-- Table --}}

@if(!empty($tickets))
    <table class="table table-items">
        <thead>
        <tr>
            <th scope="col" class="border-0 p-0 items-title">Tickets</th>
            <th scope="col" class="text-right border-0 p-0">Price</th>
            <th scope="col" class="text-right border-0 p-0">Sum</th>
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
@endif

@if(!empty($spaces))
    <table class="table table-items">
        <thead>
        <tr>
            <th scope="col" class="border-0 p-0 items-title">Spaces</th>
            <th scope="col" class="border-0 p-0">Duration</th>
            <th scope="col" class="text-right border-0 p-0">Price</th>
            <th scope="col" class="text-right border-0 p-0">Sum</th>
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

@if(!empty($services))
    <table class="table table-items">
        <thead>
        <tr>
            <th scope="col" class="border-0 p-0 items-title">Services</th>
            <th scope="col" class="border-0 p-0">Duration</th>
            <th scope="col" class="text-right border-0 p-0">Price</th>
            <th scope="col" class="text-right border-0 p-0">Sum</th>
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

@if(!empty($products))
    <table class="table table-items">
        <thead>
        <tr>
            <th scope="col" class="border-0 p-0 items-title">Products</th>
            <th scope="col" class="border-0 p-0">Variant</th>
            <th scope="col" class="border-0 p-0">Quantity</th>
            <th scope="col" class="text-right border-0 p-0">Price</th>
            <th scope="col" class="text-right border-0 p-0">Sum</th>
        </tr>
        </thead>
        <tbody>

        {{-- Items --}}
        @foreach($products as $item)
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
                <td colspan="3" class="border-0"></td>
                <td class="text-right p-0">Total</td>
                <td class="text-right p-0 total-amount">
                    {{ $invoice->formatCurrency($invoice->getTotal($products)) }}
                </td>
            </tr>
        </tbody>
    </table>
@endif

@if($invoice->notes)
    <p>
        {{ trans('invoices::invoice.notes') }}: {!! $invoice->notes !!}
    </p>
@endif

<script type="text/php">
    if (isset($pdf) && $PAGE_COUNT > 1) {
        $text = "Page {PAGE_NUM} / {PAGE_COUNT}";
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
