<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fees;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class LocalSetController extends Controller
{
    public function index()
    {
        $fees = Fees::pluck('amount', 'fee_type')->toArray();
        $userRole = Role::where('name', auth()->user()->role)->first();
        
        return view('biu_genset.local_set', compact('fees', 'userRole'));
    }

    public function updateFees(Request $request)
    {
        try {
            DB::beginTransaction();

            Fees::updateOrCreate(
                ['fee_type' => 'Application Fee'],
                ['amount' => $request->application_fee]
            );

            Fees::updateOrCreate(
                ['fee_type' => 'Reconnection Fee'],
                ['amount' => $request->reconnection_fee]
            );

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Fees updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to update fees: ' . $e->getMessage()]);
        }
    }
}
