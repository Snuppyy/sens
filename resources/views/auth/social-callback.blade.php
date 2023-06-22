<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', substr(app()->getLocale(), 0, 2)) }}">
<head>
    <title>@lang('Nova Creation Lab')</title>
</head>
<body>
    <p>...</p>
    <script>
        window.opener.location.href = '{{ $redirect }}';
        window.close();
    </script>
</body>
</html>