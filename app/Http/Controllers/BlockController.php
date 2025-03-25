<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Role;
use App\Models\Consumer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockController extends Controller
{
    public function index()
    {
        try {
            $blocks = Block::orderBy('block_id')
                ->paginate(10); 

            $user = Auth::user();
            $userRole = Role::where('name', $user->role)->first();
            
            return view('biu_genset.manage_block', [
                'blocks' => $blocks,
                'userRole' => $userRole
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching blocks: ' . $e->getMessage());
            return view('biu_genset.manage_block', [
                'blocks' => collect([]),
                'userRole' => null
            ]);
        }
    }

    public function addBarangay(Request $request, $blockId)
    {
        
    }

    public function destroy($blockId)
    {
        try {
            $block = Block::findOrFail($blockId);
            
            $consumersCount = Consumer::where('block_id', $blockId)->count();
            
            if ($consumersCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete this block as it is being used by {$consumersCount} consumer(s)",
                    'isUsedByConsumers' => true
                ]);
            }

            $block->delete();
            return response()->json([
                'success' => true,
                'message' => 'Block deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the block',
                'isUsedByConsumers' => false
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $exists = Block::where('block_id', $request->block_id)->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Block ID already exists.',
                    'field' => 'new_block_id'
                ]);
            }

            $newBarangays = array_filter(explode("\n", $request->barangays));
            $newBarangays = array_map('trim', $newBarangays);

            $existingBlock = Block::whereJsonContains('barangays', $newBarangays)->first();
            if ($existingBlock) {
                $duplicateBarangays = array_intersect($existingBlock->barangays, $newBarangays);
                return response()->json([
                    'success' => false,
                    'message' => 'These barangays already exist in Block ' . $existingBlock->block_id . ': ' . implode(', ', $duplicateBarangays),
                    'field' => 'barangays'
                ]);
            }

            $block = Block::create([
                'block_id' => $request->block_id,
                'barangays' => $newBarangays
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Block added successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add block: ' . $e->getMessage()
            ]);
        }
    }

    public function edit($blockId)
    {
        try {
            $block = Block::findOrFail($blockId);
            return response()->json([
                'success' => true,
                'block' => [
                    'block_id' => $block->block_id,
                    'barangays' => is_array($block->barangays) ? implode("\n", $block->barangays) : $block->barangays
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch block details'
            ]);
        }
    }

    public function update(Request $request, $blockId)
    {
        try {
            $exists = Block::where('block_id', $request->block_id)
                ->where('block_id', '!=', $blockId)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Block ID already exists.',
                    'field' => 'edit_block_id'
                ]);
            }

            $newBarangays = array_filter(explode("\n", $request->barangays));
            $newBarangays = array_map('trim', $newBarangays);

            $existingBlock = Block::where('block_id', '!=', $blockId)
                ->whereJsonContains('barangays', $newBarangays)
                ->first();
                
            if ($existingBlock) {
                $duplicateBarangays = array_intersect($existingBlock->barangays, $newBarangays);
                return response()->json([
                    'success' => false,
                    'message' => 'These barangays already exist in Block ' . $existingBlock->block_id . ': ' . implode(', ', $duplicateBarangays),
                    'field' => 'edit_barangays'
                ]);
            }

            $block = Block::findOrFail($blockId);
            $block->update([
                'block_id' => $request->block_id,
                'barangays' => $newBarangays
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Block updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update block: ' . $e->getMessage()
            ]);
        }
    }
}
