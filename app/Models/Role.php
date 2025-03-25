<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $primaryKey = 'role_id';
    protected $fillable = ['name'];

    public function users()
    {
        return $this->hasMany(User::class, 'role', 'name');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission', 'role_id', 'permission_id');
    }

    public function isAdministrator()
    {
        return $this->name === 'Administrator';
    }

    public function hasPermission($permissionSlug)
    {
        if ($this->isAdministrator() && $this->isEssentialPermission($permissionSlug)) {
            return true;
        }
        
        return $this->permissions()
            ->where('slug', $permissionSlug)
            ->exists();
    }

    private function isEssentialPermission($slug)
    {
        $essentialPermissions = [
            'access-dashboard',
            'access-consumers',
            'access-utilities',
            'access-settings',
            'view-role-management',
            'add-new-role',
            'edit-role',
            'manage-role-permissions',
            'delete-role'
        ];
        return in_array($slug, $essentialPermissions);
    }

    public function hasAnyPermission($permissions)
    {
        if (is_string($permissions)) {
            $permissions = [$permissions];
        }
        
        return $this->permissions()
            ->whereIn('slug', $permissions)
            ->exists();
    }
}