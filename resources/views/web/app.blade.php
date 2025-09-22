<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>

    <title>{{ $title ?? 'Imperia' }}</title>

    <link href="{{ asset('/css/app.css') }}" rel="stylesheet" />
    <script>
      // Build default props from the current request and merge with any provided $props from the controller
      @php
        $defaultProps = [
            'initialPath' => request()->getPathInfo(),
            'query' => request()->query(),
            'restaurant_id' => request()->route('restaurant_id') ?? request()->route('restaurant') ?? null,
            'menu_id' => request()->route('menu_id') ?? null,
            'csrfToken' => csrf_token(),
        ];
        // Gather all view data (e.g. variables passed from controllers like 'restaurant', 'menus')
        $viewData = collect(get_defined_vars())->except(['__env','app','errors','__data','__path','defaultProps','viewData','finalProps']);
        // Allow explicit 'props' array to override computed values
        $fromControllerProps = (isset($props) && is_array($props)) ? $props : [];
        $finalProps = array_merge($defaultProps, $viewData->toArray(), $fromControllerProps);
      @endphp
      window.__APP_PROPS__ = {!! json_encode($finalProps, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
    </script>
    <script src="{{ mix('/js/app.js') }}" defer></script>
  </head>
  <body data-theme="light">
    <div id="app"></div>
  </body>
</html>
