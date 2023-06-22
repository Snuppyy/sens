<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <title>{{ config('app.name') }}</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons">
  <link rel="stylesheet" href="{{ mix('css/spa/app.css') }}">
  <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
  <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
</head>
<body>
  <div id="app"></div>

  @php
    $config = [
        'appName' => config('app.name'),
        'locale' => $locale = app()->getLocale()
    ];
  @endphp
  <script>window.config = {!! json_encode($config); !!};</script>

  {{-- Polyfill some features via polyfill.io --}}
  @php
    $polyfills = [
        'Promise',
        'Object.assign',
        'Object.values',
        'Array.prototype.find',
        'Array.prototype.findIndex',
        'Array.prototype.includes',
        'String.prototype.includes',
        'String.prototype.startsWith',
        'String.prototype.endsWith',
    ];
  @endphp
  <script src="https://cdn.polyfill.io/v2/polyfill.min.js?features={{ implode(',', $polyfills) }}"></script>

  @if (!file_exists(public_path('/hot')))
    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/common.js') }}"></script>
    <script src="{{ mix('js/spa/vendor.js') }}"></script>
  @endif
  <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
  <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
  <script src="{{ mix('js/spa/app.js') }}"></script>
</body>
</html>
