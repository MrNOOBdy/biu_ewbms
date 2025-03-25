<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notice;
use App\Models\Role;
use App\Models\Bill_rate;
use App\Models\Consumer;
use App\Models\LocalSet;

class DashController extends Controller
{
    public function dashboard()
    {
        $userRole = Role::where('name', auth()->user()->role)->first();
        $totalConsumers = Consumer::count();
        $billRates = Bill_rate::all()->groupBy('type');
        $type = Bill_rate::all()->groupBy('type');

        if (!$userRole) {
            Log::error("User role not found for user ID: {$user->id}");
            Auth::logout();
            return redirect()->route('adm_login.form')
                ->with('error', 'User role not found. There seems to be an error adding the user. Please contact the administrator.');
        }
        
        return view('biu_dashboard.dashboard', compact('totalConsumers', 'billRates', 'userRole'));
    }
}