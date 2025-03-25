<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\ConsumerReading;
use Illuminate\Http\Request;

class MRController extends Controller
{
    public function index()
    {
        $blocks = Block::all();
        $readings = ConsumerReading::with('consumer')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('biu_meter.meter_read', compact('readings', 'blocks'));
    }
}