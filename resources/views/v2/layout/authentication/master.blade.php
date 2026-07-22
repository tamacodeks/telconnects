<!DOCTYPE html>
@php
  $appName = defined('APP_NAME') ? APP_NAME : config('app.name');
  $appLogoFile = (defined('APP_LOGO') && APP_LOGO && file_exists(public_path('images/' . APP_LOGO))) ? APP_LOGO : 'logo.png';
  $appLogoUrl = asset('images/' . $appLogoFile);
  $sessionThemeMode = strtolower((string) session('theme', session('app_theme', 'light')));
  $initialThemeMode = in_array($sessionThemeMode, ['dark', 'dark-only', 'dark_mode', 'dark-mode'], true) ? 'dark' : 'light';
@endphp
<html lang="{{ session('locale', app()->getLocale()) }}" class="{{ $initialThemeMode === 'dark' ? 'dark' : '' }}" data-bs-theme="{{ $initialThemeMode }}">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $appName }}">
    <meta name="keywords" content="{{ $appName }}">
    <meta name="author" content="pixelstrap">
    <link rel="icon" href="{{ $appLogoUrl }}" type="image/png">
    <link rel="shortcut icon" href="{{ $appLogoUrl }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
     <title>@hasSection('title')@yield('title')@else{{ $appName }}@endif</title>
    <script>
      (function () {
        try {
          var root = document.documentElement;
          var storedMode = localStorage.getItem("mode")
            || localStorage.getItem("theme")
            || localStorage.getItem("app_theme")
            || localStorage.getItem("v2-theme")
            || "";
          var isDark = /dark/i.test(String(storedMode)) || root.getAttribute("data-bs-theme") === "dark";
          root.classList.toggle("dark", isDark);
          root.setAttribute("data-bs-theme", isDark ? "dark" : "light");
          root.style.colorScheme = isDark ? "dark" : "light";
        } catch (error) {}
      })();
    </script>
    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&amp;display=swap" rel="stylesheet">
    
    @include('v2.layout.authentication.css')
    @yield('style') 
    @include('v2.layout.theme', ['themeContext' => 'auth'])
  </head>
  <body data-bs-theme="{{ $initialThemeMode }}" class="{{ $initialThemeMode === 'dark' ? 'dark-mode dark-only' : '' }}">
    <script>
      (function () {
        try {
          var isDark = document.documentElement.getAttribute("data-bs-theme") === "dark";
          document.body.classList.toggle("dark-only", isDark);
          document.body.classList.toggle("dark-mode", isDark);
          document.body.setAttribute("data-bs-theme", isDark ? "dark" : "light");
          document.body.style.colorScheme = isDark ? "dark" : "light";
        } catch (error) {}
      })();
    </script>
    <!-- login page start-->
    @yield('content')  
    <!-- latest jquery-->
    @include('v2.layout.authentication.script') 
  </body>
</html>
