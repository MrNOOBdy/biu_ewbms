<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use HasApiTokens;

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

    public function meterReaderBlocks()
    {
        return $this->hasMany(MeterReaderBlock::class, 'user_id', 'user_id');
    }

    public function absences()
    {
        return $this->hasMany(MeterReaderSub::class, 'absent_reader_id', 'user_id');
    }

    public function substitutions()
    {
        return $this->hasMany(MeterReaderSub::class, 'substitute_reader_id', 'user_id');
    }

    public function activeSubstitution()
    {
        return $this->substitutions()
                    ->where('status', 'active')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->first();
    }

    public function activeAbsence()
    {
        return $this->absences()
                    ->where('status', 'active')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->first();
    }
}
