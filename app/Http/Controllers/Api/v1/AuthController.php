<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\MeterReaderBlock;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|exists:users',
            'password' => 'required|string',
        ]);
        $user = User::where('username', $request->username)->first();
        $assigned_block = MeterReaderBlock::where('user_id', $user->user_id)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return [
                'message' => 'The password is incorrect.'
            ];
        }

        $token = $user->createToken($user->username);
        return response()->json([
            'user' => $user,
            'assigned_block' => $assigned_block->block_id,
            'token' => $token->plainTextToken
        ]);
    }
    
    public function connect()
    {
        return response()->json(
            'Connected to network'
        );
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'Logged out'
        ]);
    }
}
