<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <title>@yield('title', system_setting_locale('meta_title', 'InnoShop DebugBar'))</title>
</head>

<body>
  @dump($data)
</body>

</html>
