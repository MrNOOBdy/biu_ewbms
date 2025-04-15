<?php

namespace App\Http\Controllers;

use App\Models\ServiceFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceRepCont extends Controller
{
    public function index()
    {
        $userRole = Auth::user()->role()->first();

        try {
            $serviceFees = ServiceFee::with(['consumer' => function($query) {
                $query->with('block');
            }])->orderBy('updated_at', 'desc')->paginate(20);
            
            $totalAmounts = ServiceFee::selectRaw('
                SUM(service_amount_paid) as total_service_amount,
                SUM(reconnection_fee) as total_reconnection_fee
            ')->first();
            
            $blocks = $serviceFees->pluck('consumer.block_id')->unique()->sort();

            $dates = ServiceFee::whereNotNull('updated_at')
                ->get()
                ->map(function($payment) {
                    return [
                        'month' => $payment->updated_at->format('n'),
                        'month_name' => $payment->updated_at->format('F'),
                        'year' => $payment->updated_at->format('Y')
                    ];
                });

            $availableMonths = $dates->unique('month')->sortBy('month')->values()
                ->map(function($date) {
                    return [
                        'number' => $date['month'],
                        'name' => $date['month_name']
                    ];
                });

            $availableYears = $dates->pluck('year')->unique()->sort()->reverse();

            return view('biu_report.service_rep', compact(
                'serviceFees',
                'blocks',
                'availableMonths',
                'availableYears',
                'userRole',
                'totalAmounts'
            ));
        } catch (\Exception $e) {
            \Log::error('Error in ServiceRepCont@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading service fee data');
        }
    }

    public function search(Request $request)
    {
        try {
            $query = $request->get('query');
            $blockFilter = $request->get('block');
            $monthFilter = $request->get('month');
            $yearFilter = $request->get('year');
            
            $serviceFees = ServiceFee::with(['consumer' => function($query) {
                $query->with('block');
            }]);

            if (!empty($query)) {
                $serviceFees->whereHas('consumer', function($q) use ($query) {
                    $q->where('firstname', 'LIKE', "%{$query}%")
                      ->orWhere('middlename', 'LIKE', "%{$query}%")
                      ->orWhere('lastname', 'LIKE', "%{$query}%");
                });
            }

            if (!empty($blockFilter)) {
                $serviceFees->whereHas('consumer', function($q) use ($blockFilter) {
                    $q->where('block_id', $blockFilter);
                });
            }

            if (!empty($monthFilter) || !empty($yearFilter)) {
                $serviceFees->where(function($q) use ($monthFilter, $yearFilter) {
                    if (!empty($monthFilter)) {
                        $q->whereMonth('updated_at', $monthFilter);
                    }
                    if (!empty($yearFilter)) {
                        $q->whereYear('updated_at', $yearFilter);
                    }
                });
            }

            $data = $serviceFees->orderBy('updated_at', 'desc')->get();

            $result = $data->map(function($fee) {
                return [
                    'block' => $fee->consumer ? "Block " . ($fee->consumer->block_id ?? 'N/A') : 'N/A',
                    'consumer_name' => $fee->consumer ? 
                        "{$fee->consumer->firstname} {$fee->consumer->middlename} {$fee->consumer->lastname}" : 'N/A',
                    'service_amount' => number_format($fee->service_amount_paid, 2),
                    'reconnection_fee' => number_format($fee->reconnection_fee, 2),
                    'status' => $fee->service_paid_status,
                    'payment_date' => $fee->updated_at ? $fee->updated_at->format('M d, Y h:i A') : 'N/A'
                ];
            });

            $totals = [
                'service_amount' => $data->sum('service_amount_paid'),
                'reconnection_fee' => $data->sum('reconnection_fee')
            ];

            return response()->json([
                'success' => true,
                'payments' => $result,
                'totals' => $totals
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search payments: ' . $e->getMessage()
            ]);
        }
    }

    public function printReport(Request $request)
    {
        try {
            $query = $request->get('query');
            $blockFilter = $request->get('block');
            $monthFilter = $request->get('month');
            $yearFilter = $request->get('year');
            
            $serviceFees = ServiceFee::with(['consumer' => function($query) {
                $query->with('block');
            }]);

            $payments = $serviceFees->orderBy('updated_at', 'desc')->get();

            $totals = [
                'service_amount' => $payments->sum('service_amount_paid'),
                'reconnection_fee' => $payments->sum('reconnection_fee')
            ];

            return view('receipts.service_fee_report', compact('payments', 'totals', 'monthFilter', 'yearFilter'));
        } catch (\Exception $e) {
            \Log::error('Error in ServiceRepCont@printReport: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating report');
        }
    }
}
