<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Bill_rate;
use App\Models\Consumer;
use App\Models\Cov_date;
use App\Models\ConsumerReading;

class BillPayController extends Controller
{
    public function showPayments()
    {
        $bills = ConsumerReading::with(['consumer', 'billPayments'])
            ->whereHas('billPayments')
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
        try {
            $bill = ConsumerReading::with(['billPayments', 'consumer'])->findOrFail($billId);
            $totalAmount = $bill->calculateBill();
            
            return response()->json([
                'present_reading' => $bill->present_reading,
                'consumption' => $bill->calculateConsumption(),
                'total_amount' => $totalAmount,
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching bill details'
            ], 500);
        }
    }

    public function processPayment(Request $request)
    {
        try {
            $bill = ConsumerReading::with('billPayments')->findOrFail($request->bill_id);
            
            if (!$bill->billPayments) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bill not found or not confirmed yet'
                ], 404);
            }

            $amountTendered = (float)$request->bill_tendered_amount;
            $penaltyAmount = (float)$request->penalty_amount;
            $billAmount = $bill->calculateBill();
            $totalAmount = $billAmount + $penaltyAmount;

            if ($amountTendered !== $totalAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Amount tendered must match the total amount exactly'
                ]);
            }

            DB::beginTransaction();
            try {
                $bill->billPayments->update([
                    'bill_status' => 'paid',
                    'bill_tendered_amount' => $amountTendered,
                    'penalty_amount' => $penaltyAmount,
                    'total_amount' => $totalAmount
                ]);
                
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function printReceipt($billId)
    {
        try {
            $bill = ConsumerReading::with(['consumer', 'billPayments'])
                ->where('consread_id', $billId)
                ->firstOrFail();
            
            if (!$bill->billPayments) {
                throw new \Exception('Bill payments not found');
            }
            
            return view('receipts.bill_receipt', compact('bill'));
        } catch (\Exception $e) {
            \Log::error('Error in BillPayController@printReceipt: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating bill receipt');
        }
    }

    public function search(Request $request)
    {
        try {
            $query = ConsumerReading::with(['consumer', 'billPayments'])
                ->whereHas('billPayments');

            if ($request->has('query')) {
                $searchTerm = $request->get('query');
                $query->whereHas('consumer', function($q) use ($searchTerm) {
                    $q->where('customer_id', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('firstname', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('lastname', 'LIKE', "%{$searchTerm}%");
                });
            }

            if ($request->has('status')) {
                $status = $request->get('status');
                if (!empty($status)) {
                    $query->whereHas('billPayments', function($q) use ($status) {
                        $q->where('bill_status', $status);
                    });
                }
            }

            $bills = $query->orderBy('reading_date', 'desc')->get();

            return response()->json([
                'success' => true,
                'bills' => $bills->map(function($bill) {
                    return [
                        'consread_id' => $bill->consread_id,
                        'customer_id' => $bill->consumer->customer_id,
                        'consumer_name' => $bill->consumer->firstname . ' ' . $bill->consumer->lastname,
                        'due_date' => date('M d, Y', strtotime($bill->due_date)),
                        'previous_reading' => $bill->previous_reading,
                        'present_reading' => $bill->present_reading,
                        'consumption' => $bill->consumption,
                        'total_amount' => number_format($bill->billPayments->total_amount, 2),
                        'status' => $bill->billPayments->bill_status,
                        'raw_amount' => $bill->billPayments->total_amount
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search bills: ' . $e->getMessage()
            ]);
        }
    }
}