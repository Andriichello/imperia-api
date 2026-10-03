<!DOCTYPE html>
<html>

  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>

    <title>{{ $title ?? 'Imperia' }}</title>

    <link href="{{ mix('/css/app.css') }}" rel="stylesheet"/>
    <script src="{{ mix('/js/app.js') }}" defer></script>

    @isset($dishes_url)
      <!-- the dishes are loaded while the scripts are -->
      <link rel="preload" href="{{ $dishes_url }}" as="fetch" crossorigin="anonymous"/>
    @endisset

    @php
      $props = [
          // shared props
          "locale" => $locale ?? null,
          "supported_locales" => $supported_locales ?? null,
          "developer_email" => $developer_email ?? null,
          // specific props
          "restaurant" => $restaurant ?? null,
          "menus" => $menus ?? null,
          "reviews" => $reviews ?? null,
          "dishes_url" => $dishes_url ?? null,
      ];

      // the restaurant's brand colors instead of the default ones of `app.css`
      $brand = isset($restaurant) && $restaurant->brand_primary && $restaurant->brand_primary_content
          ? "--color-warning: {$restaurant->brand_primary}; --color-warning-content: {$restaurant->brand_primary_content};"
          : null;
    @endphp
  </head>

  <body data-theme="light" @if($brand) style="{{ $brand }}" @endif>
    <div id="app" data-props='@json($props)'>
      <!-- App Content -->
    </div>
  </body>

</html>
