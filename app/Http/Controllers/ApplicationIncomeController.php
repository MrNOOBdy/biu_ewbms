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
            }])->get();
            
            $blocks = Block::orderBy('block_id')->get();

            return view('biu_report.appli_income', compact('connPayments', 'blocks', 'userRole'));
        } catch (\Exception $e) {
            \Log::error('Error in ApplicationIncomeController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading application income data');
        }
    }
}
