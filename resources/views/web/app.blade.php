<!DOCTYPE html>
<html>

  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>

    <title>{{ $title ?? 'Imperia' }}</title>

    <link href="{{ asset('/css/app.css') }}" rel="stylesheet"/>
    <script src="{{ mix('/js/app.js') }}" defer></script>

    @php
      $props = [
          // shared props
          "auth" => $auth ?? null,
          "flash" => $flash ?? null,
          "locale" => $locale ?? null,
          "supported_locales" => $supported_locales ?? null,
          // specific props
          "restaurant" => $restaurant ?? null,
          "menus" => $menus ?? null,
      ];
    @endphp
  </head>

  <body data-theme="light">
    <div id="app" data-props='@json($props)'>
      <!-- App Content -->
    </div>
  </body>

</html>
