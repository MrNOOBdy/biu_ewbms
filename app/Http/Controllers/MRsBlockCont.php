<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Block;
use App\Models\MeterReaderBlock;
use Illuminate\Support\Facades\DB;

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

    public function getAssignedBlocks($userId)
    {
        try {
            $blocks = DB::table('meter_reader_blocks')
                ->where('user_id', $userId)
                ->pluck('block_id')
                ->toArray();

            return response()->json([
                'success' => true,
                'blocks' => $blocks
            ])->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            Log::error('Error getting assigned blocks: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get assigned blocks'
            ], 500)->header('Content-Type', 'application/json');
        }
    }
}