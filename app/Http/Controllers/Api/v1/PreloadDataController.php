<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Bill_rate;
use App\Models\Consumer;
use App\Models\ConsumerReading;
use App\Models\Cov_date;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreloadDataController extends Controller
{
    public function get_coverage_date()
    {
        $coverage_date = Cov_date::where('status', 'Open')->get();
        return response()->json([
            'coverage_date' => $coverage_date
        ]);
    }

    public function get_consumer(Request $request)
    {
        $consumers = Consumer::where('status', 'Active')
            ->where('block_id', $request->block_id)
            ->get();
        foreach ($consumers as $consumer) {
            $reading = ConsumerReading::where('customer_id', $consumer->customer_id)
                ->where('covdate_id', $request->covdate_id)
                ->orderBy("reading_date", "desc")
                ->get()->first();

            if ($reading) {
                if ($reading->syncStatus == 0) {
                    $consumer->syncStatus = $reading->syncStatus;
                    $consumer->previous_reading = $reading->present_reading;
                } else if ($reading->syncStatus == 1) {
                    $consumer->syncStatus = $reading->syncStatus;
                    $consumer->present_reading = $reading->present_reading;
                    $consumer->previous_reading = $reading->previous_reading;
                }
            } else {
                $previousReading = ConsumerReading::where('customer_id', $consumer->customer_id)
                    ->orderBy("reading_date", "desc")
                    ->get()->first();

                if ($previousReading) {
                    $consumer->previous_reading = $previousReading->present_reading;
                } else {
                    $consumer->previous_reading = 0;
                }
                $consumer->syncStatus = 0;
            }
        }
        return response()->json([
            'consumers' => $consumers
        ]);
    }

    public function get_bill_rate()
    {
        $billRate = DB::table('bill_rate')->get();
        return response()->json([
            'billRate' => $billRate
        ]);
    }
}
