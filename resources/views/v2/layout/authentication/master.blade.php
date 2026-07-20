<!DOCTYPE html>
@php
  $appName = defined('APP_NAME') ? APP_NAME : config('app.name');
  $appLogoFile = (defined('APP_LOGO') && APP_LOGO && file_exists(public_path('images/' . APP_LOGO))) ? APP_LOGO : 'logo.png';
  $appLogoUrl = asset('images/' . $appLogoFile);
@endphp
<html lang="{{ session('locale', app()->getLocale()) }}">
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
     <title>@hasSection('title')@yield('title')@else{{ $appName }}@endif</title>
    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&amp;display=swap" rel="stylesheet">
    
    @include('v2.layout.authentication.css')
    @yield('style') 
  </head>
  <body class="{{ session('theme','light') === 'dark' ? 'dark-mode' : '' }}">
    <!-- login page start-->
    @yield('content')  
    <!-- latest jquery-->
    @include('v2.layout.authentication.script') 
  </body>
</html>
