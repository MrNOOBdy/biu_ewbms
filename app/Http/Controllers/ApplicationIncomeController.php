<?php

namespace App\Http\Controllers;

use App\Models\ConnPayment;
use App\Models\Block;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Facades\Log;

class ApplicationIncomeController extends Controller
{
    public function index()
    {
        $userRole = Auth::user()->role()->first();

        try {
            $connPayments = ConnPayment::with(['consumer' => function($query) {
                $query->with('block');
            }])->orderBy('updated_at', 'desc')->paginate(20);
            
            $blocks = $connPayments->pluck('consumer.block_id')->unique()->sort();

            $dates = ConnPayment::whereNotNull('updated_at')
                ->get()
                ->map(function($payment) {
                    return [
                        'month' => $payment->updated_at->format('n'),
                        'month_name' => $payment->updated_at->format('F'),
                        'year' => $payment->updated_at->format('Y')
                    ];
                });

            $availableMonths = $dates->unique('month')
                ->sortBy('month')
                ->values()
                ->map(function($date) {
                    return [
                        'number' => $date['month'],
                        'name' => $date['month_name']
                    ];
                });

            $availableYears = $dates->pluck('year')->unique()->sort()->reverse();

            return view('biu_report.appli_income', compact(
                'connPayments', 
                'blocks', 
                'availableMonths',
                'availableYears',
                'userRole'
            ));
        } catch (\Exception $e) {
            \Log::error('Error in ApplicationIncomeController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading application income data');
        }
    }

    public function search(Request $request)
    {
        try {
            $query = $request->get('query');
            $blockFilter = $request->get('block');
            $monthFilter = $request->get('month');
            $yearFilter = $request->get('year');
            
            $connPayments = ConnPayment::with(['consumer' => function($query) {
                $query->with('block');
            }]);

            if (!empty($query)) {
                $connPayments->whereHas('consumer', function($q) use ($query) {
                    $q->where('firstname', 'LIKE', "%{$query}%")
                      ->orWhere('middlename', 'LIKE', "%{$query}%")
                      ->orWhere('lastname', 'LIKE', "%{$query}%");
                });
            }

            if (!empty($blockFilter)) {
                $connPayments->whereHas('consumer', function($q) use ($blockFilter) {
                    $q->where('block_id', $blockFilter);
                });
            }

            if (!empty($monthFilter) || !empty($yearFilter)) {
                $connPayments->where(function($q) use ($monthFilter, $yearFilter) {
                    if (!empty($monthFilter)) {
                        $q->whereMonth('updated_at', $monthFilter);
                    }
                    if (!empty($yearFilter)) {
                        $q->whereYear('updated_at', $yearFilter);
                    }
                });
            }

            $data = $connPayments->orderBy('updated_at', 'desc')->get();

            $result = $data->map(function($payment) {
                $balance = $payment->application_fee - $payment->conn_amount_paid;
                return [
                    'block' => $payment->consumer ? "Block " . ($payment->consumer->block_id ?? 'N/A') : 'N/A',
                    'consumer_name' => $payment->consumer ? 
                        "{$payment->consumer->firstname} {$payment->consumer->middlename} {$payment->consumer->lastname}" : 'N/A',
                    'application_fee' => number_format($payment->application_fee, 2),
                    'amount_paid' => number_format($payment->conn_amount_paid, 2),
                    'balance' => number_format($balance, 2),
                    'payment_date' => $payment->conn_pay_status == 'unpaid' ? 
                        'Not paid yet' : 
                        ($payment->updated_at ? $payment->updated_at->format('M d, Y h:i A') : 'Not yet paid'),
                    'raw_fee' => $payment->application_fee,
                    'raw_paid' => $payment->conn_amount_paid,
                    'raw_balance' => $balance
                ];
            });

            $totals = [
                'application_fee' => $data->sum('application_fee'),
                'amount_paid' => $data->sum('conn_amount_paid'),
                'balance' => $data->sum('application_fee') - $data->sum('conn_amount_paid')
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
            
            $connPayments = ConnPayment::with(['consumer' => function($query) {
                $query->with('block');
            }]);

            if (!empty($query)) {
                $connPayments->whereHas('consumer', function($q) use ($query) {
                    $q->where('firstname', 'LIKE', "%{$query}%")
                      ->orWhere('middlename', 'LIKE', "%{$query}%")
                      ->orWhere('lastname', 'LIKE', "%{$query}%");
                });
            }

            if (!empty($blockFilter)) {
                $connPayments->whereHas('consumer', function($q) use ($blockFilter) {
                    $q->where('block_id', $blockFilter);
                });
            }

            if (!empty($monthFilter) || !empty($yearFilter)) {
                $connPayments->where(function($q) use ($monthFilter, $yearFilter) {
                    if (!empty($monthFilter)) {
                        $q->whereMonth('updated_at', $monthFilter);
                    }
                    if (!empty($yearFilter)) {
                        $q->whereYear('updated_at', $yearFilter);
                    }
                });
            }

            $payments = $connPayments->orderBy('updated_at', 'desc')->get();

            $totals = [
                'application_fee' => $payments->sum('application_fee'),
                'amount_paid' => $payments->sum('conn_amount_paid'),
                'balance' => $payments->sum('application_fee') - $payments->sum('conn_amount_paid')
            ];

            return view('receipts.application_income_report', compact('payments', 'totals', 'monthFilter', 'yearFilter'));
        } catch (\Exception $e) {
            \Log::error('Error in ApplicationIncomeController@printReport: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating report');
        }
    }
}
