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
                BI-U: eWBS is a comprehensive electronic water billing system designed to streamline 
                the water utility management process. Our system provides efficient billing management, 
                customer account tracking, making water utility management simpler and more effective 
                for both administrators and customers.
            </p>
        </div>
        
        <!-- <div class="features-container">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <h3>Efficient Billing</h3>
                <p>Simplified billing process with automated calculations and notifications</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Account Management</h3>
                <p>Comprehensive tools for customer account tracking and management</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Data Analytics</h3>
                <p>Insightful reports and analytics to monitor water consumption patterns</p>
            </div>
        </div> -->
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