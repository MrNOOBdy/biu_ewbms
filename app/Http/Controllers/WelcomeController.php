<?php

namespace App\Http\Controllers;

use App\Services\PushbulletService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WelcomeController extends Controller
{
    public function index()
    {
        return view('biu_welcome.welcome');
    }
}
