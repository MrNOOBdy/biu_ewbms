<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Consumer;
use App\Models\ConsumerReading;
use App\Models\Cov_date;
use Illuminate\Http\Request;

class PreloadDataController extends Controller
{
    public function get_coverage_date()
    {
        $coverage_date = Cov_date::where('status', 'Open')->get();
        return [
            'coverage_date' => $coverage_date
        ];
    }

    public function get_consumer(Request $request)
    {
        // TODO: Attach consumer readings to each consumer
        $consumers = Consumer::where('status', 'Active')
            ->where('block_id', $request->block_id)
            ->get();
        return [
            'consumers' => $consumers
        ];
        
    }
}
