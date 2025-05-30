<?php

namespace App\Http\Controllers;

use App\Models\ConnPayment;
use App\Models\Consumer;
use App\Models\Block;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class ConnPayController extends Controller
{
    public function index()
    {
        $userRole = Auth::user()->role()->first();
        if (!$userRole || !$userRole->hasPermission('access-application-fee')) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        try {
            $connPayments = ConnPayment::with('consumer')
                ->whereHas('consumer')
                ->orderByRaw("CASE WHEN conn_pay_status = 'unpaid' THEN 0 ELSE 1 END")
                ->orderBy('created_at', 'desc')
                ->paginate(20);
            
            $blocks = Block::all();

            return view('biu_conpay.application', compact('connPayments', 'blocks', 'userRole'));
        } catch (\Exception $e) {
            \Log::error('Error in ConnPayController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading application payments');
        }
    }

    public function processPayment(Request $request)
    {
        $userRole = Auth::user()->role()->first();
        if (!$userRole || !$userRole->hasPermission('process-application-payment')) {
            return response()->json([
                'errors' => [
                    'amount_tendered' => ['Unauthorized to process payments']
                ]
            ], 422);
        }

        try {
            $payment = ConnPayment::where('customer_id', $request->customer_id)
                ->where('conn_pay_status', 'unpaid')
                ->first();

            if (!$payment) {
                return response()->json([
                    'errors' => [
                        'amount_tendered' => ['Invalid or already paid application']
                    ]
                ], 422);
            }

            if ($payment->conn_pay_status === 'paid') {
                return response()->json([
                    'errors' => [
                        'amount_tendered' => ['Application fee has already been paid']
                    ]
                ], 422);
            }

            $amountTendered = (float) $request->amount_tendered;
            $applicationFee = (float) $payment->application_fee;

            if ($amountTendered !== $applicationFee) {
                $message = $amountTendered < $applicationFee ? 
                    'Amount tendered is insufficient. Required amount is ₱' . number_format($applicationFee, 2) :
                    'Amount tendered is too high. Required amount is ₱' . number_format($applicationFee, 2);
                
                return response()->json([
                    'errors' => [
                        'amount_tendered' => [$message]
                    ]
                ], 422);
            }

            DB::beginTransaction();
            try {
                $currentTimestamp = now()->setTimezone('Asia/Manila');

                $payment->conn_amount_paid = $amountTendered;
                $payment->conn_pay_status = 'paid';
                $payment->updated_at = $currentTimestamp;
                $payment->save();

                $consumer = Consumer::where('customer_id', $request->customer_id)->first();
                if ($consumer) {
                    $consumer->status = 'Active';
                    $consumer->application_fee = 0;
                    $consumer->updated_at = $currentTimestamp;
                    $consumer->save();
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Application fee payment processed successfully'
                ]);

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            return response()->json([
                'errors' => [
                    'amount_tendered' => ['Error processing payment: ' . $e->getMessage()]
                ]
            ], 422);
        }
    }

    public function checkPermission()
    {
        $userRole = Auth::user()->role()->first();
        return response()->json([
            'hasPermission' => $userRole && $userRole->hasPermission('access-application-fee'),
            'canProcessPayment' => $userRole && $userRole->hasPermission('process-application-payment')
        ]);
    }

    public function printReceipt($customer_id)
    {
        $userRole = Auth::user()->role()->first();
        if (!$userRole || !$userRole->hasPermission('print-application')) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        try {
            $payment = ConnPayment::with('consumer')->where('customer_id', $customer_id)->first();
            
            if (!$payment) {
                return redirect()->back()->with('error', 'Payment record not found');
            }

            return view('receipts.application_fee', compact('payment'));
        } catch (\Exception $e) {
            \Log::error('Error in ConnPayController@printReceipt: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating receipt');
        }
    }

    public function search(Request $request)
    {
        try {
            $query = ConnPayment::with('consumer');
            
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
                    $query->where('conn_pay_status', $status);
                }
            }

            $connPayments = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'payments' => $connPayments->map(function($payment) {
                    return [
                        'customer_id' => $payment->customer_id,
                        'block_id' => $payment->consumer->block_id ?? 'N/A',
                        'firstname' => $payment->consumer->firstname ?? 'N/A',
                        'middlename' => $payment->consumer->middlename ?? 'N/A',
                        'lastname' => $payment->consumer->lastname ?? 'N/A',
                        'application_fee' => number_format($payment->application_fee, 2),
                        'amount_paid' => number_format($payment->conn_amount_paid, 2),
                        'status' => $payment->conn_pay_status,
                        'raw_fee' => $payment->application_fee,
                        'raw_paid' => $payment->conn_amount_paid
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
