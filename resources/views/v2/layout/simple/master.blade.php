<!DOCTYPE html>
@php
  $appName = defined('APP_NAME') ? APP_NAME : config('app.name');
  $sessionThemeMode = strtolower((string) session('theme', session('app_theme', 'light')));
  $initialThemeMode = in_array($sessionThemeMode, ['dark', 'dark-only', 'dark_mode', 'dark-mode'], true) ? 'dark' : 'light';
@endphp
<html lang="en" class="{{ $initialThemeMode === 'dark' ? 'dark' : '' }}" data-bs-theme="{{ $initialThemeMode }}">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Cuba admin is super flexible, powerful, clean &amp; modern responsive bootstrap 5 admin template with unlimited possibilities.">
    <meta name="keywords" content="admin template, Cuba admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="pixelstrap">
    <link rel="icon" href="{{asset('assets/images/favicon.png')}}" type="image/x-icon">
    <link rel="shortcut icon" href="{{asset('assets/images/favicon.png')}}" type="image/x-icon">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
    <title>{{ $appName }}@if(!empty($page_title)) :: {{ $page_title }}@endif</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&amp;display=swap" rel="stylesheet">
    @include('v2.layout.simple.css')
    @yield('style')
    @include('v2.layout.theme', ['themeContext' => 'app'])
  </head>
  
  @php
    $currentRouteName = Route::current() ? Route::current()->getName() : null;
    $bodyClasses = trim($__env->yieldContent('body_class'));

    if ($currentRouteName === 'button-builder') {
        $bodyClasses = trim($bodyClasses . ' button-builder');
    }

    $bodyThemeClasses = $initialThemeMode === 'dark' ? ' dark-mode dark-only' : '';
    $bodyClassAttribute = trim($bodyClasses . $bodyThemeClasses);
  @endphp
  <body @if($currentRouteName === 'index') onload="startTime()" @endif data-bs-theme="{{ $initialThemeMode }}" @if($bodyClassAttribute !== '') class="{{ $bodyClassAttribute }}" @endif>
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
    <div class="loader-wrapper">
      <div class="loader-index"><span></span></div>
      <svg>
        <defs></defs>
        <filter id="goo">
          <fegaussianblur in="SourceGraphic" stddeviation="11" result="blur"></fegaussianblur>
          <fecolormatrix in="blur" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 19 -9" result="goo"> </fecolormatrix>
        </filter>
      </svg>
    </div>
    <!-- tap on top starts-->
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>
    <!-- tap on tap ends-->
    <!-- page-wrapper Start-->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
      <!-- Page Header Start-->
      @include('v2.layout.simple.header')
      <!-- Page Header Ends  -->
      <!-- Page Body Start-->
      <div class="page-body-wrapper">
        <!-- Page Sidebar Start-->
        @include('v2.layout.simple.sidebar')
        <!-- Page Sidebar Ends-->
        <div class="page-body">
          <!-- Container-fluid starts-->
          @yield('content')
          <!-- Container-fluid Ends-->
        </div>
        <!-- footer start-->
        @include('v2.layout.simple.footer') 
        
      </div>
    </div>
    <!-- latest jquery-->
    @include('v2.layout.simple.script')  
    @yield('script')
    @yield('scripts')
    @stack('scripts')

    <!-- Plugin used-->

  </body>
</html>
