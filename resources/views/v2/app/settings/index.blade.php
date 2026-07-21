@extends('v2.layout.simple.master')

@section('title', 'Application Settings')

@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/common.css') }}">
<link rel="stylesheet" href="{{ asset('css/v2/app-settings-v2.css') }}?v={{ @filemtime(public_path('css/v2/app-settings-v2.css')) ?: time() }}">
@endsection

@include('v2.layout.simple.breadcrumb', ['data' => [], 'show_current_breadcrumb' => false])

@php
  $languages = [
    ['folder' => 'en', 'name' => 'English'],
    ['folder' => 'fr', 'name' => 'French'],
  ];
  $logoFile = $settings['APP_LOGO'] ?: 'logo_inverse.png';
  $logoExists = file_exists(public_path('images/' . $logoFile));
  $logoUrl = asset('images/' . ($logoExists ? $logoFile : 'logo.png'));
@endphp

@section('content')
@include('v2.app.settings.partials.form')
@endsection

@section('script')
<script src="{{ asset('js/v2/app-settings-v2.js') }}?v={{ @filemtime(public_path('js/v2/app-settings-v2.js')) ?: time() }}"></script>
@endsection
