<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Notice;
use App\Models\Role;
use App\Models\Bill_rate;
use App\Models\Consumer;
use App\Models\LocalSet;
use App\Models\ConsumerReading;
use App\Models\ConsBillPay;
use App\Models\Cov_date;
use App\Models\ConnPayment;
use App\Models\ServiceFee;

class DashController extends Controller
{
    public function dashboard()
    {
        $userRole = Role::where('name', auth()->user()->role)->first();
        
        $activeCoverage = Cov_date::where('status', Cov_date::STATUS_OPEN)->first();
        
        $totalConsumers = Consumer::count();
        $billRates = Bill_rate::all()->groupBy('consumer_type');
        
        $totalBills = ConsBillPay::join('consumer_reading', 'consumer_bill_pay.consread_id', '=', 'consumer_reading.consread_id')
            ->when($activeCoverage, function($query) use ($activeCoverage) {
                return $query->whereBetween('consumer_reading.reading_date', [
                    $activeCoverage->coverage_date_from, 
                    $activeCoverage->coverage_date_to
                ]);
            })->count();
        
        $unpaidBills = ConsBillPay::join('consumer_reading', 'consumer_bill_pay.consread_id', '=', 'consumer_reading.consread_id')
            ->where('consumer_bill_pay.bill_status', 'Unpaid')
            ->when($activeCoverage, function($query) use ($activeCoverage) {
                return $query->whereBetween('consumer_reading.reading_date', [
                    $activeCoverage->coverage_date_from, 
                    $activeCoverage->coverage_date_to
                ]);
            })->count();

        $waterBillIncome = ConsBillPay::where('bill_status', 'Paid')
            ->when($activeCoverage, function($query) use ($activeCoverage) {
                return $query->whereBetween('updated_at', [
                    $activeCoverage->coverage_date_from, 
                    $activeCoverage->coverage_date_to
                ]);
            })->sum('total_amount');

        $applicationFees = ConnPayment::when($activeCoverage, function($query) use ($activeCoverage) {
            return $query->whereBetween('created_at', [
                $activeCoverage->coverage_date_from, 
                $activeCoverage->coverage_date_to
            ]);
        })->where('conn_pay_status', 'Paid')
          ->sum('conn_amount_paid');

        $serviceFees = ServiceFee::when($activeCoverage, function($query) use ($activeCoverage) {
            return $query->whereBetween('created_at', [
                $activeCoverage->coverage_date_from, 
                $activeCoverage->coverage_date_to
            ]); 
        })->where('service_paid_status', 'Paid')
          ->sum('reconnection_fee');

        $totalIncome = $waterBillIncome + $applicationFees + $serviceFees;

        $monthlyConsumption = ConsumerReading::selectRaw('MONTH(cr.reading_date) as month, SUM(cr.consumption) as total_consumption')
            ->from('consumer_reading as cr')
            ->join('coverage_date as cd', 'cr.covdate_id', '=', 'cd.covdate_id')
            ->whereYear('cd.coverage_date_from', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total_consumption', 'month')
            ->toArray();

        $yearlyConsumption = ConsumerReading::selectRaw('YEAR(cd.coverage_date_from) as year, SUM(cr.consumption) as total_consumption')
            ->from('consumer_reading as cr')
            ->join('coverage_date as cd', 'cr.covdate_id', '=', 'cd.covdate_id')
            ->groupBy('year')
            ->orderBy('year')
            ->get()
            ->pluck('total_consumption', 'year')
            ->toArray();

        if (!$userRole) {
            Log::error("User role not found for user ID: {$user->id}");
            Auth::logout();
            return redirect()->route('adm_login.form')  
                ->with('error', 'User role not found. There seems to be an error adding the user. Please contact the administrator.');
        }
        
        return view('biu_dashboard.dashboard', compact(
            'totalConsumers',
            'billRates',
            'userRole',
            'totalBills',
            'unpaidBills',
            'totalIncome',
            'monthlyConsumption',
            'yearlyConsumption',
            'activeCoverage'
        ));
    }
}