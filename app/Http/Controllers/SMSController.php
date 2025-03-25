<?php

namespace App\Http\Controllers;

use App\Services\PushbulletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SMSController extends Controller
{
    protected $pushbullet;

    public function __construct(PushbulletService $pushbullet)
    {
        $this->pushbullet = $pushbullet;
    }

    public function sendSMS(Request $request)
    {
        try {
            $request->validate([
                'phoneNumber' => 'required|string|regex:/^09\d{9}$/',
                'message' => 'required|string'
            ]);

            $phoneNumber = '+63' . substr($request->input('phoneNumber'), 1);
            $message = $request->input('message');
            $senderName = 'BI-U: eWBS';
            $isOTP = str_contains(strtolower($message), 'otp') || $request->input('isOTP', false);

            Log::info('New SMS request received', [
                'phone' => $phoneNumber,
                'sender' => $senderName,
                'message_length' => strlen($message),
                'isOTP' => $isOTP,
                'timestamp' => now()->toDateTimeString()
            ]);

            $result = $this->pushbullet->sendSMS($phoneNumber, $message, $senderName, $isOTP);
            
            return response()->json([
                'success' => $result,
                'message' => $result ? 'SMS sent successfully!' : 'Failed to send SMS. Check logs for details.'
            ]);
        } catch (\Exception $e) {
            Log::error('SMS Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
