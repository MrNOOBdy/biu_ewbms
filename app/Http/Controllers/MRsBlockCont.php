<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Block;
use App\Models\MeterReaderBlock;
use Illuminate\Support\Facades\DB;
use App\Models\MeterReaderSubstitution;
use Carbon\Carbon;
use App\Models\MeterReaderSub;

class MRsBlockCont extends Controller
{
    public function index()
    {
        $meterReaders = User::where('role', 'Meter Reader')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        foreach($meterReaders as $reader) {
            $reader->assigned_blocks = DB::table('meter_reader_blocks')
                ->where('user_id', $reader->user_id)
                ->pluck('block_id')
                ->toArray();
        }

        $blocks = Block::all();
        return view('biu_meter.mrsblock', compact('meterReaders', 'blocks'));
    }

    public function assignBlocks(Request $request)
    {
        try {
            $request->validate([
                'reader_id' => 'required|exists:users,user_id',
                'blocks' => 'required|array',
                'blocks.*' => 'exists:blocks,block_id'
            ]);

            $existingAssignments = DB::table('meter_reader_blocks')
                ->whereIn('meter_reader_blocks.block_id', $request->blocks)
                ->where('meter_reader_blocks.user_id', '!=', $request->reader_id)
                ->join('users', 'meter_reader_blocks.user_id', '=', 'users.user_id')
                ->select(
                    'meter_reader_blocks.block_id',
                    'users.firstname',
                    'users.lastname'
                )
                ->get();

            if ($existingAssignments->isNotEmpty()) {
                $errorMessages = [];
                foreach ($existingAssignments as $assignment) {
                    $errorMessages[] = "Block {$assignment->block_id} is already assigned to {$assignment->firstname} {$assignment->lastname}";
                }

                return response()->json([
                    'success' => false,
                    'errors' => [
                        'blocks' => $errorMessages
                    ]
                ], 422);
            }

            DB::beginTransaction();
            
            DB::table('meter_reader_blocks')
                ->where('user_id', $request->reader_id)
                ->delete();

            $assignments = array_map(function($blockId) use ($request) {
                return [
                    'user_id' => $request->reader_id,
                    'block_id' => $blockId,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }, $request->blocks);

            DB::table('meter_reader_blocks')->insert($assignments);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Blocks assigned successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Block assignment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign blocks'
            ], 500);
        }
    }

    public function createSubstitution(Request $request)
    {
        try {
            $validated = $request->validate([
                'absent_reader_id' => 'required|exists:users,user_id',
                'substitute_reader_id' => 'required|exists:users,user_id|different:absent_reader_id',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string|min:3'
            ]);

            $existingAbsentSub = MeterReaderSub::where('absent_reader_id', $validated['absent_reader_id'])
                ->where('status', 'active')
                ->where(function ($query) use ($validated) {
                    $query->where(function ($q) use ($validated) {
                        $q->where('start_date', '<=', $validated['start_date'])
                           ->where('end_date', '>=', $validated['start_date']);
                    })->orWhere(function ($q) use ($validated) {
                        $q->where('start_date', '<=', $validated['end_date'])
                           ->where('end_date', '>=', $validated['end_date']);
                    });
                })->first();

            if ($existingAbsentSub) {
                return response()->json([
                    'success' => false,
                    'message' => 'This meter reader already has an active substitution during this period'
                ], 422);
            }
            
            $substitution = MeterReaderSub::create([
                'absent_reader_id' => $validated['absent_reader_id'],
                'substitute_reader_id' => $validated['substitute_reader_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'reason' => $validated['reason'],
                'status' => 'active'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Substitution created successfully',
                'data' => $substitution->load(['absentReader', 'substituteReader'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Substitution validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Substitution creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create substitution: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSubstitutions(Request $request)
    {
        $substitutions = MeterReaderSub::with(['absentReader', 'substituteReader'])
            ->where(function ($query) use ($request) {
                if ($request->has('status')) {
                    $query->where('status', $request->status);
                }
                if ($request->has('date')) {
                    $date = Carbon::parse($request->date);
                    $query->where('start_date', '<=', $date)
                        ->where('end_date', '>=', $date);
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $substitutions
        ]);
    }

    public function updateSubstitution(Request $request, $id)
    {
        try {
            $substitution = MeterReaderSub::findOrFail($id);
            $substitution->update([
                'status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Substitution updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Substitution update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update substitution'
            ], 500);
        }
    }

    public function getAssignedBlocks($userId)
    {
        try {
            $activeSubstitution = MeterReaderSub::where('substitute_reader_id', $userId)
                ->where('status', 'active')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();

            if ($activeSubstitution) {
                $blocks = DB::table('meter_reader_blocks')
                    ->where('user_id', $activeSubstitution->absent_reader_id)
                    ->pluck('block_id')
                    ->toArray();
            } else {
                $blocks = DB::table('meter_reader_blocks')
                    ->where('user_id', $userId)
                    ->pluck('block_id')
                    ->toArray();
            }

            return response()->json([
                'success' => true,
                'blocks' => $blocks
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting assigned blocks: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get assigned blocks'
            ], 500);
        }
    }

    public function search(Request $request)
    {
        try {
            $query = User::where('role', 'Meter Reader');
            
            if ($request->has('query')) {
                $searchTerm = $request->get('query');
                $query->where(function($q) use ($searchTerm) {
                    $q->where('firstname', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('lastname', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('contactnum', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                });
            }

            $meterReaders = $query->orderBy('created_at', 'desc')->get();

            foreach($meterReaders as $reader) {
                $reader->assigned_blocks = DB::table('meter_reader_blocks')
                    ->where('user_id', $reader->user_id)
                    ->pluck('block_id')
                    ->toArray();
            }

            return response()->json([
                'success' => true,
                'readers' => $meterReaders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search meter readers: ' . $e->getMessage()
            ]);
        }
    }
}