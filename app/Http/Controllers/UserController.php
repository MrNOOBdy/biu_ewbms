<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(20);
        $roles = Role::all();
        
        $userRole = Role::where('name', auth()->user()->role)->first();
        $canAddUser = $userRole ? $userRole->hasPermission('add-new-user') : false;
        
        return view('biu_utilities.user_acc', compact('users', 'roles', 'canAddUser', 'userRole'));
    }

    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'contactnum' => 'required|numeric|digits:11|unique:users,contactnum',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|max:255',
            'status' => 'required|string|in:activate,deactivate',
        ], [
            'contactnum.unique' => 'This contact number is already registered with another user.',
            'username.unique' => 'This username is already taken. Please choose a different username.',
            'email.unique' => 'This email address is already registered with another user.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'The password must be at least 6 characters.',
            'role.required' => 'Please select a role for this user.'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()->toArray()
                ], 422);
            }
        
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        

        try {
            $currentTimestamp = now()->setTimezone('Asia/Manila');
            
            User::create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'contactnum' => $request->contactnum,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'status' => $request->status,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User created successfully'
                ]);
            }
            
            return redirect()->route('users.index')
                ->with('success', 'User created successfully');
                
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create user'
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to create user')
                ->withInput();
        }
    }

    public function deactivate($id)
    {
        try {
            $user = User::findOrFail($id);
            
            if (auth()->id() == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deactivate your own account while logged in.'
                ], 403);
            }

            $user->update(['status' => 'deactivate']);
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate user'
            ], 500);
        }
    }

    public function activate($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->update(['status' => 'activate']);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to activate user'], 500);
        }
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = DB::table('roles')->get();
        return view('user.upt_user', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'contactnum' => "required|numeric|digits:11|unique:users,contactnum,{$user->user_id},user_id",
                'username' => "required|string|max:255|unique:users,username,{$user->user_id},user_id",
                'email' => "required|email|unique:users,email,{$user->user_id},user_id",
                'role' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $currentTimestamp = now()->setTimezone('Asia/Manila');

            $user->update(array_merge(
                $request->only([
                    'firstname',
                    'lastname',
                    'contactnum',
                    'username',
                    'email',
                    'role'
                ]),
                ['updated_at' => $currentTimestamp]
            ));
            
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showResetPasswordForm($id)
    {
        $user = User::findOrFail($id);
        return view('user.reset_password', compact('user'));
    }

    public function resetPassword(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'new_password' => 'required|string|min:6|confirmed',
            ], [
                'new_password.confirmed' => 'The password confirmation does not match.',
                'new_password.min' => 'The password must be at least 6 characters.',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = User::findOrFail($id);
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password has been updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update password'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            if (auth()->id() == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account while logged in.'
                ], 400);
            }

            $user = User::findOrFail($id);
            
            $adminCount = User::where('role', 'Admin')
                ->where('status', 'activate')
                ->count();
                
            if ($user->role === 'Admin' && $adminCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete the last active admin account.'
                ], 400);
            }

            $totalUsers = User::count();
            if ($totalUsers <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete the last user account.'
                ], 400);
            }

            $user->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'User successfully deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user'
            ], 500);
        }
    }

    public function create()
    {
        $userRole = Role::where('name', auth()->user()->role)->first();
        if (!$userRole || !$userRole->hasPermission('add-new-user')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to add new users'
            ], 403);
        }

        $roles = Role::all();
        return view('user.add_user', compact('roles'));
    }

    public function verifyUserPassword(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password is required'
                ], 422);
            }

            $user = User::findOrFail($id);
            if (Hash::check($request->password, $user->password)) {
                return response()->json(['success' => true]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while verifying the password'
            ], 500);
        }
    }

    public function verifyDeletePassword(Request $request, $user)
    {
        try {
            $password = $request->json('password');
            $userToDelete = User::findOrFail($user);

            if (empty($password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password is required'
                ], 422);
            }

            $authenticated = Hash::check($password, $userToDelete->password);

            return response()->json([
                'success' => $authenticated,
                'message' => $authenticated ? 'Password verified successfully' : 'Incorrect password'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify password'
            ], 500);
        }
    }
}