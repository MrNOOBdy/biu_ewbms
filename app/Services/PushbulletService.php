<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class PushbulletService
{
    protected $apiKey;
    protected $apiUrl = 'https://api.pushbullet.com/v2';
    protected $systemName = 'BI-U: eWater Billing System';

    public function __construct()
    {
        $this->apiKey = env('PUSHBULLET_API_KEY');
    }

    public function sendSMS($phoneNumber, $message, $senderName = null, $isOTP = false)
    {
        $senderName = $senderName ?? $this->systemName;
        
        $formattedMessage = "[{$this->systemName}]\n\n{$message}";

        if ($isOTP) {
            $otpKey = "otp_request_{$phoneNumber}";
            if (Cache::has($otpKey)) {
                Log::info('OTP request prevented - recent request exists', ['phone' => $phoneNumber]);
                return true;
            }
            
            $sessionKey = "otp_sent_{$phoneNumber}";
            if (Session::has($sessionKey)) {
                Log::info('OTP request prevented - session check', ['phone' => $phoneNumber]);
                return true;
            }
        }

        $cacheKey = "sms_throttle_{$phoneNumber}";
        if (Cache::has($cacheKey)) {
            Log::info('SMS sending prevented - throttle active', ['phone' => $phoneNumber]);
            return true;
        }

        try {
            $devices = $this->getDevices();
            $phoneDevice = collect($devices)->first(function ($device) {
                return !empty($device['has_sms']) && $device['has_sms'] === true;
            });

            if (!$phoneDevice) {
                Log::error('No SMS-capable device found');
                return false;
            }

            $messageWithSender = "$senderName:\n$message";
            
            $response = Http::withToken($this->apiKey)
                ->post($this->apiUrl . '/texts', [
                    'data' => [
                        'target_device_iden' => $phoneDevice['iden'],
                        'addresses' => [$phoneNumber],
                        'message' => $formattedMessage
                    ]
                ]);

            if ($response->successful()) {
                Cache::put($cacheKey, true, now()->addMinutes(2));
            }

            if ($response->successful() && $isOTP) {
                Cache::put("otp_request_{$phoneNumber}", true, now()->addMinutes(5));
                Session::put("otp_sent_{$phoneNumber}", true);
            }

            if (!$response->successful()) {
                Log::error('SMS send failed: ' . $response->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());
            return false;
        }
    }

    private function getDevices()
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->get($this->apiUrl . '/devices');

            if ($response->successful()) {
                return $response->json()['devices'] ?? [];
            }
            

            Log::error('Failed to get devices: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('Error getting devices: ' . $e->getMessage());
            return [];
        }
    }
}
