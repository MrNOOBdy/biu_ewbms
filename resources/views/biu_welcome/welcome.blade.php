@extends('biu_layout.app')

@section('title', 'BI-U: Welcome')

@section('content')
<head>
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
</head>
<div class="welcome-container">
    <div class="hero-section">
        <div class="logo-container">
            <img src="{{ asset('images/logo/bi-u_logo.png') }}" alt="BI-U Logo" class="main-logo">
            <img src="{{ asset('images/logo/logo2.png') }}" alt="Secondary Logo" class="secondary-logo">
        </div>
        <h1 class="welcome-title">Bien Unido Electronic Water Billing System</h1>
        <div class="title-underline"></div>
    </div>
    
    <div class="welcome-content">
        <div class="welcome-text-container">
            <p class="welcome-text">
                BI-U: eWBS is a comprehensive electronic water billing system designed to streamline the water utility management process. Our system provides convenient and efficient way to manage water bills and consumer account tracking, making water utility management simpler and more effective for both administrators and consumers.

     This system will be able to process bill payments, generate billing reports and issues official receipt. The system will also have a feature that notifies registered consumers about their monthly bills, upcoming payment due dates and overdue payments via SMS.
            </p>
        </div>
    </div>
    
    <div class="action-container">
        @if(Auth::check())
            <a href="{{ route('dashboard') }}" class="admin-btn">Admin Dashboard</a>
        @else
            <a href="{{ route('adm_login.form') }}" class="admin-btn">Login to System</a>
        @endif
    </div>
    
    <div class="footer-info">
        <p>© 2025 Bien Unido Municipality. All rights reserved.</p>
    </div>
</div>
@endsection