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

class DashController extends Controller
{
    public function dashboard()
    {
        $userRole = Role::where('name', auth()->user()->role)->first();
        $totalConsumers = Consumer::count();
        $billRates = Bill_rate::all()->groupBy('consumer_type');
        
        $totalBills = ConsumerReading::count();
        
        $unpaidBills = ConsBillPay::where('bill_status', 'Unpaid')->count();
        
        $totalIncome = ConsBillPay::where('bill_status', 'Paid')
                                 ->sum('total_amount');

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
            'yearlyConsumption'
        ));
    }
}