<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Role;
use App\Models\Bill_rate;
use App\Models\Consumer;
use App\Models\Cov_date;
use App\Models\ConsumerReading;
use App\Models\ConsBillPay;
use App\Models\Block;

class BillingController extends Controller
{
    public function latestBills()
    {
        $blocks = Block::all();
        $consumer = Consumer::all();
        $bills = ConsBillPay::with(['consumer'])
        ->orderBy('reading_date', 'desc')
        ->paginate(10);

        return view('biu_billing.latest_bills', compact('bills', 'blocks', 'consumer'));
    }
}