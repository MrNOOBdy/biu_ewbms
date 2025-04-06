<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConsBillPay;
use App\Models\ConsumerReading;
use App\Models\Consumer;
use App\Models\Cov_date;
use Carbon\Carbon;

class ReportBillController extends Controller
{
    public function income_index()
    {
        $query = ConsBillPay::query()
            ->select(
                'water_consumers.block_id',
                'water_consumers.firstname',
                'water_consumers.lastname',
                'consumer_bill_pay.total_amount',
                'consumer_bill_pay.created_at'
            )
            ->join('consumer_reading', 'consumer_bill_pay.consread_id', '=', 'consumer_reading.consread_id')
            ->join('water_consumers', 'consumer_reading.customer_id', '=', 'water_consumers.customer_id')
            ->where('consumer_bill_pay.bill_status', 'Paid');

        $totalIncome = $query->sum('consumer_bill_pay.total_amount');
        
        $paidBills = $query->orderBy('consumer_bill_pay.created_at', 'desc')
                          ->paginate(10)
                          ->through(function($bill) {
                              $bill->date_paid = Carbon::parse($bill->created_at);
                              return $bill;
                          });

        return view('biu_report.income_rep', compact('paidBills', 'totalIncome'));
    }

    public function balance_index()
    {
        $query = ConsBillPay::query()
            ->select(
                'water_consumers.block_id',
                'water_consumers.firstname',
                'water_consumers.lastname',
                'consumer_bill_pay.total_amount',
                'consumer_reading.due_date'
            )
            ->join('consumer_reading', 'consumer_bill_pay.consread_id', '=', 'consumer_reading.consread_id')
            ->join('water_consumers', 'consumer_reading.customer_id', '=', 'water_consumers.customer_id')
            ->where('consumer_bill_pay.bill_status', 'Unpaid');

        $totalBalance = $query->sum('consumer_bill_pay.total_amount');
        
        $unpaidBills = $query->orderBy('consumer_reading.due_date', 'desc')
                            ->paginate(10)
                            ->through(function($bill) {
                                $bill->due_date = Carbon::parse($bill->due_date);
                                return $bill;
                            });

        return view('biu_report.balance_rep', compact('unpaidBills', 'totalBalance'));
    }

    public function searchBalance(Request $request)
    {
        try {
            $query = ConsBillPay::query()
                ->select(
                    'water_consumers.block_id',
                    'water_consumers.firstname',
                    'water_consumers.lastname',
                    'consumer_bill_pay.total_amount',
                    'consumer_reading.due_date'
                )
                ->join('consumer_reading', 'consumer_bill_pay.consread_id', '=', 'consumer_reading.consread_id')
                ->join('water_consumers', 'consumer_reading.customer_id', '=', 'water_consumers.customer_id')
                ->where('consumer_bill_pay.bill_status', 'Unpaid');

            if ($request->has('query')) {
                $searchTerm = $request->get('query');
                $query->where(function($q) use ($searchTerm) {
                    $q->where('water_consumers.firstname', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('water_consumers.lastname', 'LIKE', "%{$searchTerm}%");
                });
            }

            if ($request->has('block')) {
                $block = $request->get('block');
                if (!empty($block)) {
                    $query->where('water_consumers.block_id', $block);
                }
            }

            $bills = $query->orderBy('consumer_reading.due_date', 'desc')->get();
            $totalBalance = $bills->sum('total_amount');

            return response()->json([
                'success' => true,
                'bills' => $bills->map(function($bill) {
                    return [
                        'block_id' => $bill->block_id,
                        'consumer_name' => $bill->firstname . ' ' . $bill->lastname,
                        'total_amount' => number_format($bill->total_amount, 2),
                        'due_date' => $bill->due_date ? Carbon::parse($bill->due_date)->format('M d, Y') : 'N/A',
                        'raw_amount' => $bill->total_amount
                    ];
                }),
                'totalBalance' => $totalBalance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search balances: ' . $e->getMessage()
            ]);
        }
    }

    public function searchIncome(Request $request)
    {
        try {
            $query = ConsBillPay::query()
                ->select(
                    'water_consumers.block_id',
                    'water_consumers.firstname',
                    'water_consumers.lastname',
                    'consumer_bill_pay.total_amount',
                    'consumer_bill_pay.created_at'
                )
                ->join('consumer_reading', 'consumer_bill_pay.consread_id', '=', 'consumer_reading.consread_id')
                ->join('water_consumers', 'consumer_reading.customer_id', '=', 'water_consumers.customer_id')
                ->where('consumer_bill_pay.bill_status', 'Paid');

            if ($request->has('query')) {
                $searchTerm = $request->get('query');
                $query->where(function($q) use ($searchTerm) {
                    $q->where('water_consumers.firstname', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('water_consumers.lastname', 'LIKE', "%{$searchTerm}%");
                });
            }

            if ($request->has('month')) {
                $month = $request->get('month');
                if (!empty($month)) {
                    $query->whereMonth('consumer_bill_pay.created_at', $month);
                }
            }

            if ($request->has('year')) {
                $year = $request->get('year');
                if (!empty($year)) {
                    $query->whereYear('consumer_bill_pay.created_at', $year);
                }
            }

            $bills = $query->orderBy('consumer_bill_pay.created_at', 'desc')->get();
            $totalIncome = $bills->sum('total_amount');

            return response()->json([
                'success' => true,
                'bills' => $bills->map(function($bill) {
                    return [
                        'block_id' => $bill->block_id,
                        'consumer_name' => $bill->firstname . ' ' . $bill->lastname,
                        'total_amount' => number_format($bill->total_amount, 2),
                        'date_paid' => Carbon::parse($bill->created_at)->format('M d, Y'),
                        'raw_amount' => $bill->total_amount
                    ];
                }),
                'totalIncome' => $totalIncome
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search income: ' . $e->getMessage()
            ]);
        }
    }
}
