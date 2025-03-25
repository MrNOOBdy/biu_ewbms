<?php

namespace App\Http\Controllers;

use App\Services\PushbulletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PassResetController extends Controller
{
    protected $pushbullet;

    public function __construct(PushbulletService $pushbullet)
    {
        $this->pushbullet = $pushbullet;
    }

    public function showForgotMethod()
    {
        session(['forgotPasswordClicked' => true]);
        return view('resetpass.forgot_method');
    }

    private function ensurePasswordResetStarted()
    {
        if (!session()->has('forgotPasswordClicked')) {
            return redirect()->route('adm_login.form')
                ->with('error', 'Please click "Forgot password" to access this page.');
        }
        return null;
    }

    public function showForgotForm()
    {
        if ($redirect = $this->ensurePasswordResetStarted()) {
            return $redirect;
        }
        return view('resetpass.email_method');
    }

    public function sendResetLink(Request $request)
    {
        if ($redirect = $this->ensurePasswordResetStarted()) {
            return $redirect;
        }

        $request->validate([
            'username' => 'required',
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)
                    ->where('username', $request->username)
                    ->first();

        if (!$user) {
            return back()->withErrors(['email' => 'We cannot find a user with that username and email combination.']);
        }

        try {
            $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            DB::table('password_resets')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => Hash::make($verificationCode),
                    'created_at' => now()
                ]
            );

            Mail::to($user->email)->send(new ResetPasswordMail($verificationCode, $user->username));
            
            session(['reset_email' => $user->email]);
            
            return redirect()->route('password.verify.code.form')
                    ->with('status', 'Verification code has been sent to your email.');
                    
        } catch (\Exception $e) {
            \Log::error('Password reset email error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Failed to send verification code. Please try again.']);
        }
    }

    public function showVerifyCodeForm()
    {
        if ($redirect = $this->ensurePasswordResetStarted()) {
            return $redirect;
        }
        
        if (!session('reset_email')) {
            return redirect()->route('password.request');
        }
        
        return view('resetpass.verify-code');
    }

    public function verifyCode(Request $request)
    {
        if ($redirect = $this->ensurePasswordResetStarted()) {
            return $redirect;
        }

        $request->validate([
            'code' => 'required|digits:6',
            'password' => 'required|confirmed|min:6'
        ]);

        $email = session('reset_email');
        if (!$email) {
            return redirect()->route('password.request');
        }

        try {
            $passwordReset = DB::table('password_resets')
                ->where('email', $email)
                ->first();

            if (!$passwordReset || !Hash::check($request->code, $passwordReset->token)) {
                return back()->withErrors(['code' => 'Invalid verification code.']);
            }

            if ($passwordReset->created_at < now()->subMinutes(10)) {
                return back()->withErrors(['code' => 'Verification code has expired. Please request a new one.']);
            }

            $user = User::where('email', $email)->first();
            $user->update(['password' => Hash::make($request->password)]);

            DB::table('password_resets')->where('email', $email)->delete();
            session()->forget(['reset_email', 'forgotPasswordClicked']);

            return redirect()->route('adm_login.form')
                ->with('status', 'Password has been reset successfully.');

        } catch (\Exception $e) {
            Log::error('Password reset error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An error occurred while resetting your password.']);
        }
    }

    public function showForgotSmsForm()
    {
        if ($redirect = $this->ensurePasswordResetStarted()) {
            return $redirect;
        }

        return view('resetpass.sms_method');
    }

    public function sendResetOtp(Request $request)
    {
        if ($redirect = $this->ensurePasswordResetStarted()) {
            return $redirect;
        }

        $request->validate([
            'username' => 'required',
            'phone_number' => 'required|regex:/^09\d{9}$/'
        ]);

        $user = User::where('username', $request->username)
                    ->where('contactnum', $request->phone_number)
                    ->first();

        if (!$user) {
            return back()->withErrors(['phone_number' => 'We cannot find a user with that username and phone number combination.']);
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        DB::table('password_resets')->updateOrInsert(
            ['phone_number' => $request->phone_number],
            [
                'token' => Hash::make($otp),
                'created_at' => now()
            ]
        );

        $message = "Your BI-U Water password reset OTP is: $otp. Valid for 10 minutes.";
        $phoneNumber = '+63' . substr($request->phone_number, 1);
        
        $sent = $this->pushbullet->sendSMS($phoneNumber, $message);

        if (!$sent) {
            return back()->withErrors(['phone_number' => 'Failed to send OTP. Please try again.']);
        }

        return redirect()->route('password.verify.otp.form')
            ->with(['phone_number' => $request->phone_number, 'status' => 'OTP has been sent to your phone.']);
    }

    public function showVerifyOtpForm()
    {
        if ($redirect = $this->ensurePasswordResetStarted()) {
            return $redirect;
        }

        if (!session('phone_number')) {
            return redirect()->route('password.request.sms');
        }

        return view('resetpass.verify-otp');
    }

    public function verifyResetOtp(Request $request)
    {
        if ($redirect = $this->ensurePasswordResetStarted()) {
            return $redirect;
        }

        $request->validate([
            'phone_number' => 'required',
            'otp' => 'required|digits:6',
            'password' => 'required|confirmed|min:6'
        ]);

        try {
            $passwordReset = DB::table('password_resets')
                ->where('phone_number', $request->phone_number)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$passwordReset) {
                return redirect()->route('password.request.sms')
                    ->withErrors(['error' => 'Password reset request not found. Please try again.']);
            }

            if (!Hash::check($request->otp, $passwordReset->token)) {
                return back()->withErrors(['otp' => 'Invalid OTP code.']);
            }

            if ($passwordReset->created_at < now()->subMinutes(10)) {
                return back()->withErrors(['otp' => 'OTP code has expired. Please request a new one.']);
            }

            $user = User::where('contactnum', $request->phone_number)->first();
            if (!$user) {
                return back()->withErrors(['error' => 'User not found.']);
            }

            $user->update([
                'password' => Hash::make($request->password)
            ]);

            DB::table('password_resets')
                ->where('phone_number', $request->phone_number)
                ->delete();

            Session::forget('phone_number');
            Cache::forget("otp_request_{$request->phone_number}");
            Cache::forget("sms_throttle_{$request->phone_number}");

            session()->forget('forgotPasswordClicked');

            return redirect()->route('adm_login.form')
                ->with('status', 'Password has been reset successfully. Please login with your new password.');

        } catch (\Exception $e) {
            Log::error('Password reset error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An error occurred while resetting your password.']);
        }
    }
}