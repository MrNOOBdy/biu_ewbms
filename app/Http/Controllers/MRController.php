<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\ConsumerReading;
use App\Models\Cov_date;
use Illuminate\Http\Request;

class MRController extends Controller
{
    public function index()
    {
        $blocks = Block::all();
        $currentCoverage = Cov_date::getCurrentCoverage();
        
        $readings = collect([]);
        if ($currentCoverage) {
            $readings = ConsumerReading::with('consumer')
                ->where('covdate_id', $currentCoverage->covdate_id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        return view('biu_meter.meter_read', compact('readings', 'blocks', 'currentCoverage'));
    }

    public function search(Request $request)
    {
        try {
            $currentCoverage = Cov_date::getCurrentCoverage();
            if (!$currentCoverage) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active coverage period found'
                ]);
            }

            $query = ConsumerReading::with('consumer')
                ->where('covdate_id', $currentCoverage->covdate_id);
            
            if ($request->has('block') && !empty($request->get('block'))) {
                $blockFilter = $request->get('block');
                $query->whereHas('consumer', function($q) use ($blockFilter) {
                    $q->where('block_id', '=', $blockFilter);
                });
            }

            if ($request->has('query') && !empty($request->get('query'))) {
                $searchTerm = $request->get('query');
                $query->where(function($q) use ($searchTerm) {
                    $q->whereHas('consumer', function($subQ) use ($searchTerm) {
                        $subQ->where('firstname', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('lastname', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('customer_id', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('consumer_type', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhere('meter_reader', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('present_reading', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('consumption', 'LIKE', "%{$searchTerm}%");
                });
            }

            $readings = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'readings' => $readings->map(function($reading) {
                    return [
                        'block_id' => $reading->consumer->block_id,
                        'customer_id' => $reading->customer_id,
                        'consumer_name' => $reading->consumer->firstname . ' ' . $reading->consumer->lastname,
                        'consumer_type' => $reading->consumer->consumer_type,
                        'previous_reading' => $reading->previous_reading,
                        'present_reading' => $reading->present_reading,
                        'consumption' => $reading->calculateConsumption(),
                        'meter_reader' => $reading->meter_reader
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search readings: ' . $e->getMessage()
            ]);
        }
    }
}