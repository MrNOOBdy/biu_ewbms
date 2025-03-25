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
    <h3>Reset Password via Email</h3>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="unauth-form-group">
            <label class="unauth-label">Username</label>
            <input type="text" name="username" value="{{ old('username') }}" required autofocus>
            <br>
            @error('username')
                <span class="error-text">{{ $message }}</span>
            @enderror
        </div>

        <div class="unauth-form-group">
            <label class="unauth-label">Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="email@email.com" required>
            <br>
            @error('email')
                <span class="error-text">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit">Send Password Reset Link</button>
    </form>

    <a href="{{ route('password.method') }}" class="reset-method-back">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Methods</span>
    </a>
</div>
@endsection
