<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    public function index()
    {
        $userRole = Role::where('name', auth()->user()->role)->first();
        
        $roles = Role::all();
        return view('biu_utilities.manage_role', compact('roles', 'userRole'));
    }

    public function store(Request $request)
    {
        try {
            $userRole = Role::where('name', auth()->user()->role)->first();

            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:roles,name|max:255'
            ], [
                'name.unique' => 'A role with this name already exists.',
                'name.required' => 'The role name is required.',
                'name.max' => 'The role name cannot exceed 255 characters.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $currentTimestamp = now()->setTimezone('Asia/Manila');
            $role = Role::create([
                'name' => $request->name,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp
            ]);

            if ($role) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role created successfully',
                    'role' => $role
                ], 200);
            }

            throw new \Exception('Failed to create role');

        } catch (\Exception $e) {
            \Log::error('Role creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($roleId)
    {
        try {
            $userRole = Role::where('name', auth()->user()->role)->first();
            
            $role = Role::findOrFail($roleId);
            
            $protectedRoles = ['Meter Reader', 'Treasurer', 'Administrator'];
            if (in_array($role->name, $protectedRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This role cannot be deleted because this are the main user role. Please contact the system developer.'
                ], 400);
            }

            if (Role::count() <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete the last role. At least one role must remain.'
                ], 400);
            }

            $usersCount = User::where('role', $role->name)->count();
            if ($usersCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete this role. It is currently assigned to {$usersCount} user(s). Please reassign these users to different roles first."
                ], 400);
            }

            $role->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Role successfully deleted'
            ]);

        } catch (\Exception $e) {
            \Log::error('Role deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role. Please try again.'
            ], 500);
        }
    }

    public function edit($roleId)
    {
        try {
            $userRole = Role::where('name', auth()->user()->role)->first();

            $role = Role::findOrFail($roleId);
            return response()->json([
                'success' => true,
                'role' => [
                    'role_id' => $role->role_id,
                    'name' => $role->name
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Role edit error:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }
    }

    public function update(Request $request, $roleId)
    {
        try {
            $userRole = Role::where('name', auth()->user()->role)->first();

            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:roles,name,' . $roleId . ',role_id|max:255'
            ], [
                'name.unique' => 'This role name is already taken.',
                'name.required' => 'The role name is required.',
                'name.max' => 'The role name cannot exceed 255 characters.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $role = Role::findOrFail($roleId);
            $oldRoleName = $role->name;
            $currentTimestamp = now()->setTimezone('Asia/Manila');
            $role->update([
                'name' => $request->name,
                'updated_at' => $currentTimestamp
            ]);

            User::where('role', $oldRoleName)->update(['role' => $request->name]);

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function showPermissions($roleId)
    {
        try {
            $userRole = Role::where('name', auth()->user()->role)->first();

            $role = Role::with('permissions')->findOrFail($roleId);
            $permissions = Permission::all();
            $userRole = Role::where('name', auth()->user()->role)->first();
            
            return view('biu_utilities.role_permi', compact('role', 'permissions', 'userRole'));
        } catch (\Exception $e) {
            \Log::error('Show permissions error:', ['error' => $e->getMessage()]);
            abort(404);
        }
    }

    public function updatePermissions(Request $request, $roleId)
    {
        try {
            $userRole = Role::where('name', auth()->user()->role)->first();

            $role = Role::findOrFail($roleId);
            $permissions = $request->input('permissions');
            
            DB::beginTransaction();
            
            $role->permissions()->detach();
            
            foreach ($permissions as $permission) {
                if ($permission['granted']) {
                    $role->permissions()->attach($permission['permission_id']);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Permissions updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Permission update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update permissions'
            ], 500);
        }
    }
}
