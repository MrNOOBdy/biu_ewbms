@extends('biu_layout.app')

@section('content')
<head>
    <link rel="stylesheet" href="{{ asset('css/unauth.css') }}">
</head>
<div class="unauth-cont">
    <div class="unauth-logo-cont">
        <img src="{{ asset('images/logo/logo2.png') }}" alt="Secondary Logo" class="unauth-logo2">
        <img src="{{ asset('images/logo/bi-u_logo.png') }}" alt="BI-U Logo" class="unauth-logo_login">
    </div>
    <h3>Choose Reset Password Method</h3>
    <a href="{{ route('password.request') }}" class="btn reset-method-btn">
        <i class="fas fa-envelope"></i>
        <span>Reset via Email</span>
    </a>
    <a href="{{ route('password.request.sms') }}" class="btn reset-method-btn">
        <i class="fas fa-mobile-alt"></i>
        <span>Reset via SMS OTP</span>
    </a>
    <a href="{{ route('adm_login.form') }}" class="reset-method-back">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Login</span>
    </a>
</div>
@endsection
