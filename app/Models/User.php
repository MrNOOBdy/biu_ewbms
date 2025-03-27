<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $primaryKey = 'user_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'firstname',
        'lastname',
        'username',
        'email',
        'password',
        'contactnum',
        'role',
        'status',
        'assigned_blocks'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'assigned_blocks' => 'array'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role', 'name');
    }

    public function hasPermission($permissionSlug)
    {
        return $this->role()->first()->permissions()
            ->where('slug', $permissionSlug)
            ->exists();
    }
}
