<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Consumer;
use App\Models\ConsumerReading;
use App\Models\Cov_date;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MRController extends Controller
{
    public function index()
    {
        $blocks = Block::all();
        $currentCoverage = Cov_date::getCurrentCoverage();
        $userRole = Auth::user()->role()->first();
        
        $readings = collect([]);
        if ($currentCoverage) {
            $readings = ConsumerReading::with('consumer')
                ->where('covdate_id', $currentCoverage->covdate_id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        return view('biu_meter.meter_read', compact('readings', 'blocks', 'currentCoverage', 'userRole'));
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

    public function update(Request $request, $id)
    {
        try {
            $reading = ConsumerReading::findOrFail($id);
            
            $request->validate([
                'present_reading' => 'required|numeric|min:' . $reading->previous_reading
            ]);

            $reading->present_reading = $request->present_reading;
            $reading->consumption = $reading->calculateConsumption();
            $reading->save();

            return response()->json([
                'success' => true,
                'message' => 'Reading updated successfully',
                'reading' => [
                    'present_reading' => $reading->present_reading,
                    'consumption' => $reading->consumption
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update reading: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getConsumers(Request $request)
    {
        try {
            $currentCoverage = Cov_date::getCurrentCoverage();
            if (!$currentCoverage) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active coverage period found'
                ]);
            }

            $query = Consumer::query();

            if ($request->has('block') && !empty($request->block)) {
                $query->where('block_id', $request->block);
            }

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('firstname', 'LIKE', "%{$search}%")
                      ->orWhere('lastname', 'LIKE', "%{$search}%")
                      ->orWhere('customer_id', 'LIKE', "%{$search}%");
                });
            }

            $consumers = $query->with(['readings' => function($q) use ($currentCoverage) {
                $q->where('covdate_id', $currentCoverage->covdate_id);
            }])->get();

            $data = $consumers->map(function($consumer) {
                $currentReading = $consumer->readings->first();
                $previousReading = ConsumerReading::where('customer_id', $consumer->customer_id)
                    ->where('covdate_id', '<>', Cov_date::getCurrentCoverage()->covdate_id)
                    ->orderBy('reading_date', 'desc')
                    ->first();

                return [
                    'block_id' => $consumer->block_id,
                    'customer_id' => $consumer->customer_id,
                    'consumer_name' => $consumer->firstname . ' ' . $consumer->lastname,
                    'consumer_type' => $consumer->consumer_type,
                    'previous_reading' => $previousReading ? $previousReading->present_reading : 0,
                    'present_reading' => $currentReading ? $currentReading->present_reading : null,
                    'has_reading' => !is_null($currentReading),
                ];
            });

            return response()->json([
                'success' => true,
                'consumers' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch consumers: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeReadings(Request $request)
    {
        try {
            $request->validate([
                'customer_id' => 'required',
                'covdate_id' => 'required|exists:coverage_date,covdate_id',
                'present_reading' => 'required|numeric|min:0'
            ]);

            $existingReading = ConsumerReading::where('customer_id', $request->customer_id)
                ->where('covdate_id', $request->covdate_id)
                ->first();

            if ($existingReading) {
                return response()->json([
                    'success' => false,
                    'message' => 'A reading already exists for this consumer in the current coverage period'
                ], 422);
            }

            $previousReading = ConsumerReading::where('customer_id', $request->customer_id)
                ->where('covdate_id', '<>', $request->covdate_id)
                ->orderBy('reading_date', 'desc')
                ->first();

            $coverageDate = Cov_date::find($request->covdate_id);
            
            $reading = new ConsumerReading([
                'customer_id' => $request->customer_id,
                'covdate_id' => $request->covdate_id,
                'reading_date' => now(),
                'due_date' => $coverageDate->due_date,
                'previous_reading' => $previousReading ? $previousReading->present_reading : 0,
                'present_reading' => $request->present_reading,
                'consumption' => $request->present_reading - ($previousReading ? $previousReading->present_reading : 0),
                'meter_reader' => auth()->user()->firstname . ' ' . auth()->user()->lastname,
            ]);

            $reading->save();

            return response()->json([
                'success' => true,
                'message' => 'Reading saved successfully',
                'reading' => $reading
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save reading: ' . $e->getMessage()
            ], 500);
        }
    }
}