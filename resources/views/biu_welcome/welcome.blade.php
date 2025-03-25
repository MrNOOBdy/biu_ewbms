@extends('biu_layout.app')

@section('title', 'BI-U: Welcome')

@section('content')
<head>
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
</head>
<div class="welcome-container">
    <div class="unauth-logo-cont">
        <img src="{{ asset('images/logo/logo2.png') }}" alt="Secondary Logo" class="unauth-logo2">
        <img src="{{ asset('images/logo/bi-u_logo.png') }}" alt="BI-U Logo" class="logo">
    </div>
    <h1 class="welcome-title">Welcome to BI-U: Electronic Water Billing System</h1>
    <p class="welcome-text">
        BI-U: eWBS is a comprehensive electronic water billing system designed to streamline 
        the water utility management process. Our system provides efficient billing management, 
        customer account tracking, making water utility management simpler and more effective 
        for both administrators and customers.
    </p>
    @if(Auth::check())
        <a href="{{ route('dashboard') }}" class="admin-btn">Go to Admin Dashboard</a>
    @else
        <a href="{{ route('adm_login.form') }}" class="admin-btn">Administrator Login</a>
    @endif
</div>
@endsection
