<?php

namespace App\Http\Controllers;

use App\Models\Consumer;
use App\Models\ConsumerReading;
use Illuminate\Http\Request;

class Consumer_BillPayController extends Controller
{
    public function show($customerId)
    {
        $consumer = Consumer::where('customer_id', $customerId)->firstOrFail();
        $billings = ConsumerReading::where('customer_id', $customerId)
                                 ->orderBy('reading_date', 'desc')
                                 ->get();

        return view('consum_billings.cons_billings', [
            'consumer' => $consumer,
            'billings' => $billings
        ]);
    }
}
