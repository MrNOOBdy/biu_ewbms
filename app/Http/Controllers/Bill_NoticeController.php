<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Role;
use App\Models\Bill_rate;
use App\Models\Consumer;
use App\Models\Cov_date;
use App\Models\ManageNotice;
use App\Models\ConsumerReading;

class Bill_NoticeController extends Controller
{

    public function noticeBill()
    {
        $notices = ManageNotice::all();
        $bills = ConsumerReading::with(['consumer'])
            ->orderBy('reading_date', 'desc')
            ->paginate(10);

        return view('biu_billing.bill_notice', compact('bills', 'notices'));
    }
}