@component('mail::message')

<div style="text-align: center; margin-bottom: 30px;">
    <div style="display: flex; justify-content: center; align-items: center; gap: 20px;">
        <img src="{{ asset('images/logo/logo2.png') }}" alt="Secondary Logo" style="max-width: 80px;">
        <img src="{{ asset('images/logo/bi-u_logo.png') }}" alt="BIU Logo" style="max-width: 140px;">
    </div>
</div>

# Password Reset Verification Code

Hi {{ $username }},

You have requested to reset your password. Here is your verification code:

# {{ $verificationCode }}

This code will expire in 10 minutes.

If you did not request a password reset, no further action is required.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
