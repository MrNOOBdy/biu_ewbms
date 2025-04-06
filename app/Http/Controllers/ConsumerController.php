<?php

namespace App\Http\Controllers;

use App\Models\Consumer;
use App\Models\Block;
use App\Models\Bill_rate;
use App\Models\Fees;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ConsumerController extends Controller
{
    public function index()
    {
        try {
            $consumers = Consumer::orderBy('created_at', 'desc')->paginate(20);
            $blocks = Block::all();
            $billRates = Bill_rate::all();
            $userRole = Auth::user()->role()->first();
            
            $fees = DB::table('fees')
                ->whereIn('fee_type', ['Application Fee'])
                ->pluck('amount', 'fee_type')
                ->toArray();

            $applicationFee = isset($fees['Application Fee']) ? $fees['Application Fee'] : 1050.00;

            return view('biu_consumer.water_consumer', [
                'consumers' => $consumers,
                'blocks' => $blocks,
                'billRates' => $billRates,
                'fees' => ['Application Fee' => $applicationFee],
                'userRole' => $userRole
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in ConsumerController@index: ' . $e->getMessage());
            return view('biu_consumer.water_consumer', [
                'consumers' => collect([]),
                'blocks' => collect([]),
                'billRates' => collect([]),
                'fees' => ['Application Fee' => 1050.00]
            ]);
        }
    }

    public function generateId($blockId)
    {
        $lastConsumer = Consumer::where('block_id', $blockId)
            ->orderBy('customer_id', 'desc')
            ->first();

        if (!$lastConsumer) {
            $newId = sprintf("B%02d-01", $blockId);
        } else {
            $parts = explode('-', $lastConsumer->customer_id);
            $number = intval($parts[1]) + 1;
            $newId = sprintf("B%02d-%02d", $blockId, $number);
        }

        return response()->json(['consumer_id' => $newId]);
    }

    public function store(Request $request)
    {
        $userRole = Auth::user()->role()->first();
        if (!$userRole || !$userRole->hasPermission('add-new-consumer')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action'], 403);
        }

        try {
            DB::beginTransaction();

            $existingConsumer = Consumer::where(function($query) use ($request) {
                $query->where('firstname', $request->firstname)
                      ->where('lastname', $request->lastname);
                
                if ($request->middlename) {
                    $query->where('middlename', $request->middlename);
                } else {
                    $query->whereNull('middlename');
                }
            })->first();

            if ($existingConsumer) {
                return response()->json([
                    'errors' => [
                        'firstname' => ['A consumer with this complete name already exists.']
                    ]
                ], 422);
            }

            $existingContact = Consumer::where('contact_no', $request->contact_no)->first();
            if ($existingContact) {
                return response()->json([
                    'errors' => [
                        'contact_no' => ['This contact number is already registered to another consumer.']
                    ]
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'block_id' => 'required|exists:blocks,block_id',
                'firstname' => 'required|string|max:100',
                'middlename' => 'nullable|string|max:100',
                'lastname' => 'required|string|max:100',
                'address' => 'required|string',
                'contact_no' => 'required|string|size:11',
                'consumer_type' => 'required|exists:bill_rate,consumer_type'
            ], [
                'block_id.required' => 'Please select a block number.',
                'firstname.required' => 'First name is required.',
                'lastname.required' => 'Last name is required.',
                'address.required' => 'Address is required.',
                'contact_no.required' => 'Contact number is required.',
                'contact_no.size' => 'Contact number must be exactly 11 digits.',
                'consumer_type.required' => 'Please select a consumer type.',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $blockId = $request->block_id;
            $lastConsumer = Consumer::where('block_id', $blockId)
                ->orderBy('customer_id', 'desc')
                ->first();

            if (!$lastConsumer) {
                $customerId = sprintf("B%02d-01", $blockId);
            } else {
                $parts = explode('-', $lastConsumer->customer_id);
                $number = intval($parts[1]) + 1;
                $customerId = sprintf("B%02d-%0" . ($number < 100 ? "2" : "3") . "d", $blockId, $number);
            }

            $applicationFee = DB::table('fees')
                ->where('fee_type', 'Application Fee')
                ->value('amount') ?? 0;

            $currentTimestamp = now()->setTimezone('Asia/Manila');

            $data = $request->all();
            $data['customer_id'] = $customerId;
            $data['status'] = 'Pending';
            $data['application_fee'] = $applicationFee;
            $data['service_fee'] = 0;
            $data['created_at'] = $currentTimestamp;
            $data['updated_at'] = $currentTimestamp;

            $consumer = Consumer::create($data);

            DB::table('conn_payment')->insert([
                'customer_id' => $customerId,
                'application_fee' => $applicationFee,
                'conn_amount_paid' => 0,
                'conn_pay_status' => 'unpaid',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Consumer added successfully with Pending status. Application fee of ₱' . number_format($applicationFee, 2) . ' has been set.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error adding consumer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adding consumer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        if (!Auth::user()->role->hasPermission('edit-consumer')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action'], 403);
        }
        try {
            $consumer = Consumer::where('customer_id', $id)->firstOrFail();
            return response()->json([
                'success' => true,
                'consumer' => $consumer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching consumer details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function view($id)
    {
        try {
            $consumer = Consumer::where('customer_id', $id)->firstOrFail();
            
            $billingsCount = DB::table('consumer_reading')
                ->where('customer_id', $id)
                ->count();
                
            $paymentsCount = DB::table('consumer_reading as cr')
                ->join('consumer_bill_pay as cbp', 'cr.consread_id', '=', 'cbp.consread_id')
                ->where('cr.customer_id', $id)
                ->count();

            $pendingBalance = DB::table('consumer_reading as cr')
                ->join('consumer_bill_pay as cbp', 'cr.consread_id', '=', 'cbp.consread_id')
                ->where('cr.customer_id', $id)
                ->where('cbp.bill_status', 'unpaid')
                ->sum('cbp.total_amount');
                
            $consumer->billings_count = $billingsCount;
            $consumer->payments_count = $paymentsCount;
            $consumer->pending_balance = $pendingBalance > 0 ? number_format($pendingBalance, 2) : '0.00';

            return response()->json([
                'success' => true,
                'consumer' => $consumer
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in view consumer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving consumer details'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $consumer = Consumer::where('customer_id', $id)->firstOrFail();

            if ($consumer->status === 'Inactive' && 
                isset($request->status) && 
                $request->status === 'Active') {
                
                $servicePayment = DB::table('service_fee_payment')
                    ->where('customer_id', $id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if (!$servicePayment || $servicePayment->service_paid_status !== 'paid') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot activate consumer. Reconnection fee must be paid first.',
                        'keepModalOpen' => true
                    ]);
                }
            }

            if ($consumer->status === 'Pending' && 
                isset($request->status) && 
                $request->status === 'Inactive') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot set Pending consumer to Inactive. Consumer must be activated first.',
                    'keepModalOpen' => true
                ]);
            }

            if ($consumer->status === 'Inactive' && 
                isset($request->status) && 
                $request->status !== 'Inactive') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot modify status of inactive consumer. Please use the reconnect feature.',
                    'keepModalOpen' => true
                ]);
            }

            $requestData = $request->except(['_token', '_method']);

            $existingConsumer = Consumer::where(function($query) use ($request) {
                $query->where('firstname', $request->firstname)
                      ->where('lastname', $request->lastname);
                
                if ($request->middlename) {
                    $query->where('middlename', $request->middlename);
                } else {
                    $query->whereNull('middlename');
                }
            })
            ->where('customer_id', '!=', $id)
            ->first();

            if ($existingConsumer) {
                return response()->json([
                    'errors' => [
                        'firstname' => ['A consumer with this complete name already exists.']
                    ]
                ], 422);
            }

            $existingContact = Consumer::where('contact_no', $request->contact_no)
                ->where('customer_id', '!=', $id)
                ->first();
                
            if ($existingContact) {
                return response()->json([
                    'errors' => [
                        'contact_no' => ['This contact number is already registered to another consumer.']
                    ]
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'block_id' => 'required|exists:blocks,block_id',
                'firstname' => 'required|string|max:100',
                'middlename' => 'nullable|string|max:100',
                'lastname' => 'required|string|max:100',
                'contact_no' => 'required|string|size:11',
                'consumer_type' => 'required|exists:bill_rate,consumer_type'
            ], [
                'contact_no.size' => 'Contact number must be exactly 11 digits.',
                'block_id.required' => 'Block number is required.',
                'consumer_type.required' => 'Consumer type is required.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $street = $request->get('street');
            $barangay = $request->get('barangay');
            if ($street && $barangay) {
                $requestData['address'] = $street . ', ' . $barangay;
            }

            if (!isset($requestData['status'])) {
                $requestData['status'] = $consumer->status;
            }

            $changes = false;
            foreach ($requestData as $key => $value) {
                if ($consumer->$key != $value) {
                    $changes = true;
                    break;
                }
            }

            if (!$changes) {
                return response()->json([
                    'success' => false,
                    'message' => 'No changes were made to the consumer details.',
                    'keepModalOpen' => true
                ]);
            }

            $validated = $request->validate([
                'block_id' => 'required|exists:blocks,block_id',
                'firstname' => 'required|string|max:100',
                'middlename' => 'nullable|string|max:100',
                'lastname' => 'required|string|max:100',
                'contact_no' => 'required|string|size:11',
                'consumer_type' => 'required|exists:bill_rate,consumer_type'
            ]);

            $oldStatus = $consumer->status;
            $newStatus = $requestData['status'];

            $applicationFee = DB::table('fees')->where('fee_type', 'Application Fee')->value('amount') ?? 0;
            $reconnectionFee = DB::table('fees')->where('fee_type', 'Reconnection Fee')->value('amount') ?? 0;

            if ($newStatus === 'Active' && $oldStatus !== 'Active') {
                $requestData['application_fee'] = $applicationFee;
                $requestData['service_fee'] = 0;
            } elseif ($newStatus === 'Inactive' && $oldStatus !== 'Inactive') {
                DB::beginTransaction();
                try {
                    $requestData['service_fee'] = $reconnectionFee;
                    
                    DB::table('service_fee_payment')->insert([
                        'customer_id' => $id,
                        'service_amount_paid' => 0,
                        'reconnection_fee' => $reconnectionFee,
                        'service_paid_status' => 'unpaid',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            if (isset($requestData['status']) && 
                $requestData['status'] === 'Active' && 
                $consumer->status !== 'Active') {
                
                $paymentStatus = DB::table('conn_payment')
                    ->where('customer_id', $id)
                    ->value('conn_pay_status');

                if ($paymentStatus !== 'paid') {
                    $userRole = Auth::user()->role()->first();
                    
                    if (!$userRole || !$userRole->hasPermission('process-application-payment')) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Cannot activate consumer. You are not authorized to process application payments. Please contact an authorized user.',
                            'keepModalOpen' => true
                        ]);
                    }

                    if (!$userRole->hasPermission('access-application-fee')) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Cannot activate consumer. You do not have permission to access application payments. Please contact an administrator.',
                            'keepModalOpen' => true
                        ]);
                    }

                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot activate consumer. Application fee must be paid first.',
                        'requirePayment' => true,
                        'keepModalOpen' => true
                    ]);
                }
            }

            $consumer->update($requestData);

            return response()->json([
                'success' => true,
                'message' => 'Consumer updated successfully!',
                'keepModalOpen' => false
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => implode("\n", array_map(function($errors) {
                    return implode("\n", $errors);
                }, $e->errors())),
                'keepModalOpen' => true
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating consumer: ' . $e->getMessage(),
                'keepModalOpen' => true
            ], 500);
        }
    }

    public function destroy($id)
    {
        $userRole = Auth::user()->role()->first();
        if (!$userRole || !$userRole->hasPermission('delete-consumer')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action'], 403);
        }

        try {
            DB::beginTransaction();

            $consumer = Consumer::where('customer_id', $id)->firstOrFail();

            $consumerReadings = DB::table('consumer_reading')->where('customer_id', $id)->get();
            foreach ($consumerReadings as $reading) {
                DB::table('consumer_bill_pay')->where('consread_id', $reading->consread_id)->delete();
            }
            DB::table('consumer_reading')->where('customer_id', $id)->delete();

            if ($consumer->status === 'Pending') {
                DB::table('conn_payment')
                    ->where('customer_id', $id)
                    ->where('conn_pay_status', 'unpaid')
                    ->delete();
            } elseif ($consumer->status === 'Inactive') {
                DB::table('service_fee_payment')
                    ->where('customer_id', $id)
                    ->where('service_paid_status', 'unpaid')
                    ->delete();
            }

            $consumer->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Consumer and all associated records deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting consumer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting consumer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reconnect($id)
    {
        $userRole = Auth::user()->role()->first();
        if (!$userRole || !$userRole->hasPermission('reconnect-consumer')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action'], 403);
        }

        try {
            $consumer = Consumer::where('customer_id', $id)->firstOrFail();
            
            $latestPayment = DB::table('service_fee_payment')
                ->where('customer_id', $id)
                ->where('reconnection_fee', '>', 0)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$latestPayment || $latestPayment->service_paid_status !== 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot activate consumer. Reconnection fee must be paid first.'
                ]);
            }

            $consumer->update([
                'status' => 'Active',
                'service_fee' => 0
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Consumer reconnected successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error reconnecting consumer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error reconnecting consumer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkReconnectionPayment($id)
    {
        try {
            $latestPayment = DB::table('service_fee_payment')
                ->where('customer_id', $id)
                ->where('reconnection_fee', '>', 0)
                ->orderBy('created_at', 'desc')
                ->first();

            return response()->json([
                'isPaid' => $latestPayment && $latestPayment->service_paid_status === 'paid'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error checking reconnection payment: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error checking payment status'
            ], 500);
        }
    }

    public function checkReconnectionStatus($customerId)
    {
        try {
            $payment = ConnPayment::where('customer_id', $customerId)
                ->where('reconnection_fee', '>', 0)
                ->first();

            return response()->json([
                'isPaid' => $payment ? $payment->service_paid_status === 'paid' : false
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error checking reconnection status'
            ], 500);
        }
    }

    public function viewBillings($id)
    {
        try {
            $consumer = Consumer::where('customer_id', $id)->firstOrFail();
            
            $billings = DB::table('consumer_reading as cr')
                ->join('consumer_bill_pay as cbp', 'cr.consread_id', '=', 'cbp.consread_id')
                ->where([
                    ['cr.customer_id', '=', $id],
                    ['cbp.bill_status', '=', 'paid']
                ])
                ->select(
                    'cr.reading_date',
                    'cr.due_date',
                    'cr.previous_reading',
                    'cr.present_reading',
                    'cr.consumption',
                    'cbp.total_amount',
                    'cbp.bill_status'
                )
                ->orderBy('cr.reading_date', 'desc')
                ->paginate(10);

            return view('consum_billings.cons_billings', compact('consumer', 'billings'));
        } catch (\Exception $e) {
            \Log::error('Error in viewBillings: ' . $e->getMessage());
            return redirect()->route('consumers.index')
                ->with('error', 'Error loading billing history');
        }
    }

    public function search(Request $request)
    {
        try {
            $query = Consumer::query();
            
            if ($request->has('query')) {
                $searchTerm = $request->get('query');
                $query->where(function($q) use ($searchTerm) {
                    $q->where('customer_id', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('firstname', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('middlename', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('lastname', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('address', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('contact_no', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('consumer_type', 'LIKE', "%{$searchTerm}%");
                });
            }

            if ($request->has('block')) {
                $blockId = $request->get('block');
                if (!empty($blockId)) {
                    $query->where('block_id', $blockId);
                }
            }

            if ($request->has('status')) {
                $status = $request->get('status');
                if (!empty($status)) {
                    $query->where('status', $status);
                }
            }

            $consumers = $query->orderBy('created_at', 'desc')->get();
            $userRole = Auth::user()->role()->first();

            return response()->json([
                'success' => true,
                'consumers' => $consumers->map(function($consumer) use ($userRole) {
                    return [
                        'customer_id' => $consumer->customer_id,
                        'block_id' => $consumer->block_id,
                        'firstname' => $consumer->firstname,
                        'middlename' => $consumer->middlename,
                        'lastname' => $consumer->lastname,
                        'address' => $consumer->address,
                        'contact_no' => $consumer->contact_no,
                        'consumer_type' => $consumer->consumer_type,
                        'status' => $consumer->status,
                        'canEdit' => $userRole->hasPermission('edit-consumer'),
                        'canDelete' => $userRole->hasPermission('delete-consumer'),
                        'canViewBillings' => $userRole->hasPermission('view-consumer-billings'),
                        'canReconnect' => $userRole->hasPermission('reconnect-consumer')
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search consumers: ' . $e->getMessage()
            ]);
        }
    }
}