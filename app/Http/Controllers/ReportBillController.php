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
        
        // Paginate before getting results
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
        
        // Paginate before getting results
        $unpaidBills = $query->orderBy('consumer_reading.due_date', 'desc')
                            ->paginate(10)
                            ->through(function($bill) {
                                $bill->due_date = Carbon::parse($bill->due_date);
                                return $bill;
                            });

        return view('biu_report.balance_rep', compact('unpaidBills', 'totalBalance'));
    }
}
