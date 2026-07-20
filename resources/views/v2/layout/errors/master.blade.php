 <!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="Tama Demat is super flexible,">
		<meta name="keywords" content="Tama Demat">
		<meta name="author" content="pixelstrap">
		<link rel="icon" href="{{asset('assets/images/favicon.png')}}" type="image/x-icon">
		<link rel="shortcut icon" href="{{asset('assets/images/favicon.png')}}" type="image/x-icon">
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<title>TamaExpress - @yield('title')</title>
		@include('v2.layout.errors.css')
		@yield('style')
	</head>
	<body>
		<!-- Loader starts-->
		<div class="loader-wrapper">
			<div class="loader-index"><span></span></div>
			<svg>
				<defs></defs>
				<filter id="goo">
					<fegaussianblur in="SourceGraphic" stddeviation="11" result="blur"></fegaussianblur>
					<fecolormatrix in="blur" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 19 -9" result="goo">    </fecolormatrix>
				</filter>
			</svg>
		</div>
		<!-- Loader ends-->
		<!-- page-wrapper Start-->
		@yield('content')		
		<!-- latest jquery-->
		@include('v2.layout.errors.script')    
	</body>
</html>