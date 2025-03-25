@extends('biu_layout.app')

@section('title', 'BI-U: Log In')

@section('content')
<head>
    <link rel="stylesheet" href="{{ asset('css/unauth.css') }}">
</head>
<div class="unauth-cont">
    <div class="unauth-logo-cont">
        <img src="{{ asset('images/logo/logo2.png') }}" alt="Secondary Logo" class="unauth-logo2">
        <img src="{{ asset('images/logo/bi-u_logo.png') }}" alt="BI-U Logo" class="unauth-logo_login">
    </div>
    @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif
    <form action="{{ route('adm_login') }}" method="POST">
        @csrf

        <div class="unauth-form-group">
            <label class="unauth-label">Enter your username</label>
            <input type="text" name="username" value="{{ old('username') }}" class="@error('credentials') is-invalid @enderror">
        </div>

        <div class="unauth-form-group">
            <label class="unauth-label">Enter your password</label>
            <div class="password-input-group">
                <input type="password" name="password" id="password" class="@error('credentials') is-invalid @enderror" required autocomplete="off">
                <button type="button" id="togglePassword" class="toggle-password">
                    <i class="fas fa-eye-slash"></i>
                </button>
            </div>
        </div>

        @if ($errors->any())
            <div class="error-messages">
                @foreach ($errors->all() as $error)
                    <p class="error-text">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="forgot-password">
            <a href="{{ route('password.method') }}"> 
                Forgot password?
            </a>
        </div>

        <button type="submit">Login</button>
    </form>
</div>
<script defer src="{{ asset('js/passwordToggle.js') }}"></script>
@endsection
