<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Role;
use App\Models\Bill_rate;
use App\Models\Consumer;
use App\Models\Cov_date;
use App\Models\ManageNotice;
use App\Models\ConsumerReading;
use App\Models\ConsBillPay;
use App\Services\PushbulletService;

class Bill_NoticeController extends Controller
{
    protected $pushbullet;

    public function __construct(PushbulletService $pushbullet)
    {
        $this->pushbullet = $pushbullet;
    }

    public function noticeBill()
    {
        $notices = ManageNotice::all();
        $bills = ConsumerReading::with(['consumer', 'billPayments'])
            ->whereHas('billPayments', function($query) {
                $query->where('bill_status', 'unpaid');
            })
            ->orderBy('reading_date', 'desc')
            ->paginate(20);

        return view('biu_billing.bill_notice', compact('bills', 'notices'));
    }

    public function getBillDetails($consreadId)
    {
        try {
            $bill = ConsumerReading::with([
                'consumer',
                'billPayments',
                'consumer.block'
            ])->findOrFail($consreadId);

            $consumption = $bill->calculateConsumption();
            $billRate = $bill->getBillRate();
            $baseCharge = $consumption <= ConsumerReading::BASE_CUBIC_LIMIT ? $billRate->value : $billRate->value;
            $excessCharges = $consumption > ConsumerReading::BASE_CUBIC_LIMIT ? 
                ($consumption - ConsumerReading::BASE_CUBIC_LIMIT) * $billRate->excess_value_per_cubic : 0;
            $totalAmount = $baseCharge + $excessCharges;

            return response()->json([
                'success' => true,
                'data' => array_merge($bill->toArray(), [
                    'consumption' => $consumption,
                    'base_rate' => $baseCharge,
                    'excess_charges' => $excessCharges,
                    'total_amount' => $totalAmount
                ])
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching bill details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching bill details'
            ], 500);
        }
    }

    public function sendNoticeSMS(Request $request)
    {
        try {
            $consreadIds = $request->bills;
            $message = preg_replace('/^BI-U Water:?\s*\n?/i', '', $request->message);
            
            $results = [];

            $bills = ConsumerReading::with(['consumer', 'billPayments'])
                ->whereIn('consread_id', $consreadIds)
                ->get();

            foreach ($bills as $bill) {
                $phoneNumber = $bill->consumer->contact_no;
                
                $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
                if (strlen($phoneNumber) === 10) {
                    $phoneNumber = '+63' . $phoneNumber;
                } elseif (strlen($phoneNumber) === 11) {
                    $phoneNumber = '+63' . substr($phoneNumber, 1);
                }

                $sent = $this->pushbullet->sendSMS($phoneNumber, $message);
                
                $results[] = [
                    'consumer_id' => $bill->consumer->customer_id,
                    'name' => $bill->consumer->firstname . ' ' . $bill->consumer->lastname,
                    'phone' => $phoneNumber,
                    'success' => $sent
                ];
            }

            return response()->json([
                'success' => true,
                'results' => $results,
                'message' => 'SMS notifications processed'
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending notice SMS: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending notifications'
            ], 500);
        }
    }
}