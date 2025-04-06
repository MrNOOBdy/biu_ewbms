<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Consumer;
use App\Models\ServiceFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index()
    {
        try {
            $userRole = Auth::user()->role()->first();

            $servicePayments = ServiceFee::with('consumer')
                ->whereHas('consumer')
                ->where('reconnection_fee', '>', 0)
                ->orderByRaw("CASE WHEN service_paid_status = 'unpaid' THEN 0 ELSE 1 END")
                ->orderBy('created_at', 'desc')
                ->paginate(20);
            
            $blocks = Block::all();

            return view('biu_conpay.service', compact('servicePayments', 'blocks', 'userRole'));
        } catch (\Exception $e) {
            \Log::error('Error in ServiceController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading reconnection payments');
        }
    }

    public function processPayment(Request $request)
    {
        $userRole = Auth::user()->role()->first();
        if (!$userRole || !$userRole->hasPermission('service-pay')) {
            return response()->json([
                'errors' => [
                    'amount_tendered' => ['Unauthorized to process payments']
                ]
            ], 422);
        }

        try {
            DB::beginTransaction();

            $payment = ServiceFee::where('customer_id', $request->customer_id)
                ->where('reconnection_fee', '>', 0)
                ->where('service_paid_status', 'unpaid')
                ->first();

            if (!$payment) {
                return response()->json([
                    'errors' => [
                        'amount_tendered' => ['Invalid or already paid reconnection fee']
                    ]
                ], 422);
            }

            $amountTendered = (float) $request->amount_tendered;
            $reconnectionFee = (float) $payment->reconnection_fee;

            if ($amountTendered !== $reconnectionFee) {
                $message = $amountTendered < $reconnectionFee ? 
                    'Amount tendered is insufficient. Required amount is ₱' . number_format($reconnectionFee, 2) :
                    'Amount tendered is too high. Required amount is ₱' . number_format($reconnectionFee, 2);
                
                return response()->json([
                    'errors' => [
                        'amount_tendered' => [$message]
                    ]
                ], 422);
            }

            $currentTimestamp = now()->setTimezone('Asia/Manila');

            $payment->service_amount_paid = $request->amount_tendered;
            $payment->service_paid_status = 'paid';
            $payment->updated_at = $currentTimestamp;
            $payment->save();

            $consumer = Consumer::where('customer_id', $request->customer_id)->first();
            if ($consumer) {
                $consumer->service_fee = 0;
                $consumer->updated_at = $currentTimestamp;
                $consumer->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reconnection fee payment processed successfully'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'errors' => [
                    'amount_tendered' => ['Error processing payment: ' . $e->getMessage()]
                ]
            ], 422);
        }
    }

    public function printReceipt($customer_id)
    {
        $userRole = Auth::user()->role()->first();
        if (!$userRole || !$userRole->hasPermission('service-print')) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        try {
            $payment = ServiceFee::with('consumer')
                ->where('customer_id', $customer_id)
                ->first();
            
            if (!$payment) {
                return redirect()->back()->with('error', 'Payment record not found');
            }

            return view('receipts.service_fee', compact('payment'));
        } catch (\Exception $e) {
            \Log::error('Error in ServiceController@printReceipt: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating receipt');
        }
    }

    public function search(Request $request)
    {
        try {
            $query = ServiceFee::with('consumer')
                ->whereHas('consumer')
                ->where('reconnection_fee', '>', 0);

            if ($request->has('query')) {
                $searchTerm = $request->get('query');
                $query->whereHas('consumer', function($q) use ($searchTerm) {
                    $q->where('customer_id', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('firstname', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('middlename', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('lastname', 'LIKE', "%{$searchTerm}%");
                });
            }

            if ($request->has('block')) {
                $blockId = $request->get('block');
                if (!empty($blockId)) {
                    $query->whereHas('consumer', function($q) use ($blockId) {
                        $q->where('block_id', $blockId);
                    });
                }
            }

            if ($request->has('status')) {
                $status = $request->get('status');
                if (!empty($status)) {
                    $query->where('service_paid_status', $status);
                }
            }

            $payments = $query->orderByRaw("CASE WHEN service_paid_status = 'unpaid' THEN 0 ELSE 1 END")
                            ->orderBy('created_at', 'desc')
                            ->get();

            return response()->json([
                'success' => true,
                'payments' => $payments->map(function($payment) {
                    return [
                        'customer_id' => $payment->customer_id,
                        'block_id' => $payment->consumer->block_id ?? 'N/A',
                        'firstname' => $payment->consumer->firstname ?? 'N/A',
                        'middlename' => $payment->consumer->middlename ?? 'N/A',
                        'lastname' => $payment->consumer->lastname ?? 'N/A',
                        'reconnection_fee' => number_format($payment->reconnection_fee, 2),
                        'amount_paid' => number_format($payment->service_amount_paid, 2),
                        'status' => $payment->service_paid_status,
                        'raw_fee' => $payment->reconnection_fee,
                        'raw_paid' => $payment->service_amount_paid
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search payments: ' . $e->getMessage()
            ]);
        }
    }
}
