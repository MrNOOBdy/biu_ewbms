<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notice;
use App\Models\Role;
use App\Models\Bill_rate;
use App\Models\Consumer;
use App\Models\LocalSet;
use App\Models\ConsumerReading;
use App\Models\ConsBillPay;
use App\Models\Cov_date;

class DashController extends Controller
{
    public function dashboard()
    {
        $userRole = Role::where('name', auth()->user()->role)->first();
        
        $activeCoverage = Cov_date::where('status', Cov_date::STATUS_OPEN)->first();
        
        $totalConsumers = Consumer::count();
        $billRates = Bill_rate::all()->groupBy('consumer_type');
        
        $totalBills = ConsumerReading::when($activeCoverage, function($query) use ($activeCoverage) {
            return $query->whereBetween('reading_date', [
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
        
        $totalIncome = ConsBillPay::join('consumer_reading', 'consumer_bill_pay.consread_id', '=', 'consumer_reading.consread_id')
            ->where('consumer_bill_pay.bill_status', 'Paid')
            ->when($activeCoverage, function($query) use ($activeCoverage) {
                return $query->whereBetween('consumer_reading.reading_date', [
                    $activeCoverage->coverage_date_from, 
                    $activeCoverage->coverage_date_to
                ]);
            })->sum('consumer_bill_pay.total_amount');

        $monthlyConsumption = ConsumerReading::selectRaw('MONTH(created_at) as month, SUM(consumption) as total_consumption')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total_consumption', 'month')
            ->toArray();

        $yearlyConsumption = ConsumerReading::selectRaw('YEAR(created_at) as year, SUM(consumption) as total_consumption')
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