<!DOCTYPE html>
<html lang="{{ $props['locale'] }}">

  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>

    <title>{{ $props['restaurant']->name }} · Page editor</title>

    <link href="{{ mix('/css/editor.css') }}" rel="stylesheet"/>
    <script src="{{ mix('/js/editor.js') }}" defer></script>
  </head>

  <body>
    <div id="editor" data-props='@json($props)'>
      <!-- Editor -->
    </div>
  </body>

</html>
