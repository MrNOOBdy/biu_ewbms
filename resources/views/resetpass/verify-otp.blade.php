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
    <h4 class="text-center mb-4">Verify OTP Code</h4>

    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.verify.otp') }}" id="otpForm">
        @csrf
        <input type="hidden" name="phone_number" value="{{ session('phone_number') }}">
        
        <div class="unauth-form-group">
            <label class="unauth-label" for="otp">Enter OTP Code</label>
            <input type="text" name="otp" id="otp" 
                   class="form-control @error('otp') is-invalid @enderror"
                   required maxlength="6" pattern="\d{6}"
                   placeholder="Enter 6-digit OTP">
            <br>
            @error('otp')
                <span class="error-text">{{ $message }}</span>
            @enderror
        </div>

        <div class="unauth-form-group">
            <label class="unauth-label" for="password">New Password</label>
            <div class="password-input-group">
                <input type="password" name="password" id="password" 
                       class="form-control @error('password') is-invalid @enderror"
                       required minlength="8">
                <button type="button" id="togglePassword" class="toggle-password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <br>
            @error('password')
                <span class="error-text">{{ $message }}</span>
            @enderror
        </div>

        <div class="unauth-form-group">
            <label class="unauth-label" for="password_confirmation">Confirm Password</label>
            <div class="password-input-group">
                <input type="password" name="password_confirmation" 
                       id="password_confirmation" class="form-control" 
                       required minlength="8">
                <button type="button" class="toggle-password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        @error('error')
            <div class="alert alert-danger">
                {{ $message }}
            </div>
        @enderror

        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
        
        <div class="text-center mt-3">
            <a href="{{ route('password.request.sms') }}" class="forgot-password-link">
                <i class="fas fa-arrow-left me-2"></i>Back to SMS Form
            </a>
        </div>
    </form>
</div>
<script defer src="{{ asset('js/passwordToggle.js') }}"></script>
@endsection
