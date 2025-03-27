<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Role;
use App\Models\Bill_rate;
use App\Models\Consumer;
use App\Models\Cov_date;
use App\Models\ConsumerReading;
use App\Models\ConsBillPay;
use App\Models\Block;
use App\Services\PushbulletService;

class BillingController extends Controller
{
    public function latestBills()
    {
        $blocks = Block::all();
        $consumer = Consumer::all();
        
        $bills = ConsumerReading::with(['consumer'])
            ->where(function($query) {
                $query->whereDoesntHave('billPayments')
                      ->orWhereHas('billPayments', function($q) {
                          $q->where('bill_status', 'unpaid');
                      });
            })
            ->orderBy('reading_date', 'desc')
            ->paginate(10);

        return view('biu_billing.latest_bills', compact('bills', 'blocks', 'consumer'));
    }

    public function getReadingDetails($consreadId)
    {
        $reading = ConsumerReading::with('consumer')->findOrFail($consreadId);
        
        // Get active coverage dates
        $coverageDate = Cov_date::where('status', Cov_date::STATUS_OPEN)->first();
        
        $reading->coverage_date = $coverageDate;
        
        return response()->json($reading);
    }

    public function addBill(Request $request)
    {
        try {
            ConsBillPay::create([
                'consread_id' => $request->consread_id,
                'total_amount' => $request->total_amount,
                'bill_tendered_amount' => 0,
                'bill_status' => 'unpaid'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bill has been added successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating bill: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Error creating bill'
            ], 500);
        }
    }

    public function getBillDetails($consreadId)
    {
        try {
            $reading = ConsumerReading::with(['consumer', 'billPayments'])->findOrFail($consreadId);
            
            if (!$reading->consumer) {
                throw new \Exception('Consumer details not found');
            }

            return response()->json([
                'success' => true,
                'data' => $reading
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching bill details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching bill details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendBillSMS(Request $request)
    {
        try {
            $reading = ConsumerReading::with(['consumer', 'billPayments'])->findOrFail($request->consread_id);

            if (!$reading->consumer) {
                throw new \Exception('Consumer not found');
            }

            $phoneNumber = $reading->consumer->contact_no;
            if (empty($phoneNumber)) {
                throw new \Exception('Consumer has no contact number');
            }

            // Format phone number if needed
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
            if (strlen($phoneNumber) === 10) {
                $phoneNumber = '+63' . $phoneNumber;
            } elseif (strlen($phoneNumber) === 11) {
                $phoneNumber = '+63' . substr($phoneNumber, 1);
            }

            $pushbullet = new PushbulletService();
            $result = $pushbullet->sendSMS(
                $phoneNumber,
                $request->message
            );

            if (!$result) {
                throw new \Exception('Failed to send SMS through Pushbullet');
            }

            return response()->json([
                'success' => true,
                'message' => 'Bill notification sent successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending bill SMS: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending bill notification: ' . $e->getMessage()
            ], 500);
        }
    }
}