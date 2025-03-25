<?php

namespace App\Http\Controllers;

use App\Models\Bill_rate;
use App\Models\Role;
use App\Models\Consumer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Bill_rateController extends Controller
{
    public function index()
    {
        try {
            $billRates = Bill_rate::all();
            $user = Auth::user();
            $userRole = Role::where('name', $user->role)->first();
            
            return view('biu_genset.bill_rate', [
                'billRates' => $billRates,
                'userRole' => $userRole
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching bill rates: ' . $e->getMessage());
            return view('biu_genset.bill_rate', [
                'billRates' => collect([]),
                'userRole' => null
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $exists = Bill_rate::where('consumer_type', $request->consumer_type)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This consumer type already exists.',
                    'field' => 'consumer_type'
                ]);
            }

            $billRate = Bill_rate::create([
                'consumer_type' => $request->consumer_type,
                'cubic_meter' => $request->cubic_meter,
                'value' => $request->value,
                'excess_value_per_cubic' => $request->excess_value_per_cubic
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bill rate added successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add bill rate: ' . $e->getMessage()
            ]);
        }
    }

    public function edit($billrateId)
    {
        try {
            $billRate = Bill_rate::findOrFail($billrateId);
            return response()->json([
                'success' => true,
                'billRate' => $billRate
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bill rate details'
            ]);
        }
    }

    public function update(Request $request, $billrateId)
    {
        try {
            $exists = Bill_rate::where('consumer_type', $request->consumer_type)
                ->where('billrate_id', '!=', $billrateId)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This consumer type already exists.',
                    'field' => 'consumer_type'
                ]);
            }

            $billRate = Bill_rate::findOrFail($billrateId);
            $billRate->update([
                'consumer_type' => $request->consumer_type,
                'cubic_meter' => $request->cubic_meter,
                'value' => $request->value,
                'excess_value_per_cubic' => $request->excess_value_per_cubic
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bill rate updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update bill rate: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy($billrateId)
    {
        try {
            $billRate = Bill_rate::findOrFail($billrateId);
            
            $consumersCount = $billRate->consumers()->count();
            
            if ($consumersCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete this bill rate as it is being used by {$consumersCount} consumer(s)",
                    'isUsedByConsumers' => true
                ]);
            }

            $billRate->delete();
            return response()->json([
                'success' => true,
                'message' => 'Bill rate deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Bill rate deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the bill rate',
                'isUsedByConsumers' => false
            ]);
        }
    }
}
