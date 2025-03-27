<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Role;
use App\Models\Bill_rate;
use App\Models\Consumer;
use App\Models\Cov_date;
use App\Models\ConsumerReading;

class BillPayController extends Controller
{
    public function showPayments()
    {
        $bills = ConsumerReading::with(['consumer', 'coverageDate'])
            ->orderBy('reading_date', 'desc')
            ->paginate(20);
        
        return view('biu_billing.bill_payment', compact('bills'));
    }

    public function storeReadings(Request $request)
    {
        try {
            $consumer = Consumer::findOrFail($request->customer_id);
            
            $lastReading = ConsumerReading::where('customer_id', $consumer->customer_id)
                ->orderBy('reading_date', 'desc')
                ->first();

            $reading = new ConsumerReading();
            $reading->customer_id = $consumer->customer_id;
            $reading->covdate_id = $request->covdate_id;
            $reading->reading_date = $request->reading_date;
            $reading->due_date = $request->due_date;
            $reading->previous_reading = $lastReading ? $lastReading->present_reading : 0;
            $reading->present_reading = $request->present_reading;
            $reading->consumption = $reading->calculateConsumption();
            $reading->total_amount = $reading->calculateBill();
            $reading->bill_status = 'UNPAID';
            $reading->save();

            return response()->json(['message' => 'Reading stored successfully', 'data' => $reading]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getBills($consumerId)
    {
        $bills = ConsumerReading::where('customer_id', $consumerId)
            ->orderBy('reading_date', 'desc')
            ->get();
            
        return response()->json($bills);
    }

    public function getBillDetails($billId)
    {
        $bill = ConsumerReading::findOrFail($billId);
        $penaltyAmount = 0;
        
        if (now()->gt($bill->due_date)) {
            $billRate = $bill->getBillRate();
            $consumption = $bill->calculateConsumption();
            $excessCubic = max(0, $consumption - ConsumerReading::BASE_CUBIC_LIMIT);
            $penaltyAmount = $excessCubic * $billRate->excess_value_per_cubic;
        }

        return response()->json([
            'present_reading' => $bill->present_reading,
            'penalty_amount' => $penaltyAmount,
            'total_amount' => $bill->total_amount + $penaltyAmount
        ]);
    }

    public function processPayment(Request $request)
    {
        try {
            $bill = ConsumerReading::findOrFail($request->bill_id);
            $amountTendered = (float)$request->amount_tendered;
            $totalDue = $bill->total_amount;

            if (now()->gt($bill->due_date)) {
                $billRate = $bill->getBillRate();
                $consumption = $bill->calculateConsumption();
                $excessCubic = max(0, $consumption - ConsumerReading::BASE_CUBIC_LIMIT);
                $penaltyAmount = $excessCubic * $billRate->excess_value_per_cubic;
                $totalDue += $penaltyAmount;
            }

            if (abs($amountTendered - $totalDue) > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'Amount tendered must match the total amount exactly'
                ]);
            }

            $bill->bill_status = 'PAID';
            $bill->payment_date = now();
            $bill->amount_paid = $totalDue;
            $bill->billpay_tendered_amount = $amountTendered;
            $bill->penalty_amount = $totalDue - $bill->total_amount;
            $bill->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed'
            ], 500);
        }
    }

    public function printBill($billId)
    {
        try {
            $bill = ConsumerReading::with(['consumer', 'coverageDate'])
                ->where('consread_id', $billId)
                ->firstOrFail();
            
            $penaltyAmount = 0;
            if (now()->gt($bill->due_date)) {
                $billRate = $bill->getBillRate();
                $consumption = $bill->calculateConsumption();
                $excessCubic = max(0, $consumption - ConsumerReading::BASE_CUBIC_LIMIT);
                $penaltyAmount = $excessCubic * $billRate->excess_value_per_cubic;
            }

            return view('receipts.water_bill', compact('bill', 'penaltyAmount'));
        } catch (\Exception $e) {
            \Log::error('Error in BillPayController@printBill: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating bill receipt');
        }
    }
}