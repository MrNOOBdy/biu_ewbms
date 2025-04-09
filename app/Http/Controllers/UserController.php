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
        if ($request->role === 'Administrator') {
            $existingAdmin = User::where('role', 'Administrator')->exists();
            if ($existingAdmin) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'errors' => ['role' => ['An Administrator account already exists. Only one Administrator is allowed.']]
                    ], 422);
                }
                return redirect()->back()
                    ->withErrors(['role' => 'An Administrator account already exists. Only one Administrator is allowed.'])
                    ->withInput();
            }
        }

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
            
            $user = User::create([
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

           $user->createToken($request->username);

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
            
            // Only check for existing admin if trying to change to Administrator role
            if ($request->role === 'Administrator' && $user->role !== 'Administrator') {
                $existingAdmin = User::where('role', 'Administrator')
                                    ->where('user_id', '!=', $id)
                                    ->exists();
                if ($existingAdmin) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'role' => ['An Administrator account already exists. Only one Administrator is allowed.']
                        ]
                    ], 422);
                }
            }

            // Add role to allowed updates if user is Administrator
            $allowedUpdates = [
                'firstname',
                'lastname',
                'contactnum',
                'username',
                'email'
            ];

            // Include role in updates if user is Administrator or not changing to Administrator
            if ($user->role === 'Administrator' || $request->role !== 'Administrator') {
                $allowedUpdates[] = 'role';
            }

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
            
            $updateData = array_merge(
                $request->only($allowedUpdates),
                ['updated_at' => $currentTimestamp]
            );

            $user->update($updateData);
            
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

    public function search(Request $request)
    {
        try {
            $query = $request->get('query');
            $roleFilter = $request->get('role');
            $statusFilter = $request->get('status');
            
            $users = User::query();

            if (!empty($query)) {
                $users->where(function($q) use ($query) {
                    $q->where('firstname', 'LIKE', "%{$query}%")
                      ->orWhere('lastname', 'LIKE', "%{$query}%")
                      ->orWhere('username', 'LIKE', "%{$query}%")
                      ->orWhere('email', 'LIKE', "%{$query}%")
                      ->orWhere('contactnum', 'LIKE', "%{$query}%");
                });
            }

            if (!empty($roleFilter)) {
                $users->where('role', $roleFilter);
            }

            if (!empty($statusFilter)) {
                $users->where('status', $statusFilter);
            }

            $users = $users->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'users' => $users->map(function($user) {
                    return [
                        'user_id' => $user->user_id,
                        'firstname' => $user->firstname,
                        'lastname' => $user->lastname,
                        'contactnum' => $user->contactnum,
                        'email' => $user->email,
                        'username' => $user->username,
                        'role' => $user->role,
                        'status' => $user->status
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search users: ' . $e->getMessage()
            ]);
        }
    }
}