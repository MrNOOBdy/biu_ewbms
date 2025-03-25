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
    
    <h3>Verify Email Code</h3>

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.verify.code') }}">
        @csrf
        <div class="unauth-form-group">
            <label class="unauth-label">Verification Code</label>
            <input type="text" name="code" required autofocus maxlength="6">
            @error('code')
                <span class="error-text">{{ $message }}</span>
            @enderror
        </div>

        <div class="unauth-form-group">
            <label class="unauth-label">New Password</label>
            <div class="password-input-group">
                <input type="password" name="password" required>
                <button type="button" id="togglePassword" class="toggle-password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            @error('password')
                <span class="error-text">{{ $message }}</span>
            @enderror
        </div>

        <div class="unauth-form-group">
            <label class="unauth-label">Confirm Password</label>
            <div class="password-input-group">
                <input type="password" name="password_confirmation" required>
                <button type="button" class="toggle-password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit">Reset Password</button>
    </form>
</div>
<script defer src="{{ asset('js/passwordToggle.js') }}"></script>
@endsection
