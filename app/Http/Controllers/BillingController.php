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
use App\Models\MeterReaderBlock;

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
            ->paginate(20);

        return view('biu_billing.latest_bills', compact('bills', 'blocks', 'consumer'));
    }

    public function getReadingDetails($consreadId)
    {
        $reading = ConsumerReading::with(['consumer', 'consumer.block'])->findOrFail($consreadId);
        
        $coverageDate = Cov_date::where('status', Cov_date::STATUS_OPEN)->first();
        $reading->coverage_date = $coverageDate;

        $meterReader = MeterReaderBlock::with('user')
            ->where('block_id', $reading->consumer->block_id)
            ->first();
        
        $reading->meter_reader = $meterReader ? "{$meterReader->user->firstname} {$meterReader->user->lastname}" : 'Not Assigned';
        
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

            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
            if (strlen($phoneNumber) === 10) {
                $phoneNumber = '+63' . $phoneNumber;
            } elseif (strlen($phoneNumber) === 11) {
                $phoneNumber = '+63' . substr($phoneNumber, 1);
            }

            $message = preg_replace('/^BI-U Water:?\s*\n?/i', '', $request->message);

            $pushbullet = new PushbulletService();
            $result = $pushbullet->sendSMS($phoneNumber, $message);

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

    public function search(Request $request)
    {
        try {
            $query = ConsumerReading::with(['consumer', 'billPayments']);

            if ($request->has('query')) {
                $searchTerm = $request->get('query');
                $query->whereHas('consumer', function($q) use ($searchTerm) {
                    $q->where('customer_id', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('firstname', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('lastname', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('contact_no', 'LIKE', "%{$searchTerm}%");
                });
            }

            if ($request->has('status')) {
                $status = $request->get('status');
                if ($status === 'Pending') {
                    $query->whereDoesntHave('billPayments');
                } elseif ($status === 'unpaid') {
                    $query->whereHas('billPayments', function($q) {
                        $q->where('bill_status', 'unpaid');
                    });
                }
            } else {
                $query->where(function($q) {
                    $q->whereDoesntHave('billPayments')
                      ->orWhereHas('billPayments', function($sq) {
                          $sq->where('bill_status', 'unpaid');
                      });
                });
            }

            $bills = $query->orderBy('reading_date', 'desc')->get();

            return response()->json([
                'success' => true,
                'bills' => $bills->map(function($bill) {
                    $billPayment = $bill->billPayments()->first();
                    return [
                        'consread_id' => $bill->consread_id,
                        'customer_id' => $bill->consumer->customer_id,
                        'contact_no' => $bill->consumer->contact_no,
                        'consumer_name' => $bill->consumer->firstname . ' ' . $bill->consumer->lastname,
                        'reading_date' => date('M d, Y', strtotime($bill->reading_date)),
                        'due_date' => date('M d, Y', strtotime($bill->due_date)),
                        'previous_reading' => $bill->previous_reading,
                        'present_reading' => $bill->present_reading,
                        'consumption' => $bill->consumption,
                        'bill_status' => !$billPayment ? 'Pending' : $billPayment->bill_status
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