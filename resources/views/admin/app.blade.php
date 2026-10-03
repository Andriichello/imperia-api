<!DOCTYPE html>
<html lang="{{ $props['locale'] }}">

  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <meta name="robots" content="noindex"/>

    <title>{{ $title }}</title>

    <link href="{{ mix('/css/admin.css') }}" rel="stylesheet"/>
    <script src="{{ mix('/js/admin.js') }}" defer></script>
  </head>

  <body>
    <div id="admin" data-page="{{ $page }}" data-props='@json($props)'>
      <!-- Admin -->
    </div>
  </body>

</html>
