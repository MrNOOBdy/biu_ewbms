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
                ->get();
            
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
}
