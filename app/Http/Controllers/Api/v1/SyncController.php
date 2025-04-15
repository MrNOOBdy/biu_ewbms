<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyncController extends Controller
{
    public function sync(Request $request)
    {
        try {
            DB::beginTransaction();
            DB::table('consumer_reading')->insert([
                'customer_id' => $request->customer_id,
                'covdate_id' => $request->covdate_id,
                'reading_date' => $request->reading_date,
                'due_date' => $request->due_date,
                'previous_reading' => $request->previous_reading,
                'present_reading' => $request->present_reading,
                'consumption' => $request->present_reading - $request->previous_reading,
                'meter_reader' => $request->meter_reader
            ]);
            DB::commit();
            return json_encode([
                'message' => 'Record synced successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return json_encode([
                'error' => $e->getMessage()
            ]);
        }
    }
}
