<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManageNotice extends Model
{
    use HasFactory;

    protected $table = 'manage_notice';
    protected $primaryKey = 'notice_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'type',
        'announcement'
    ];
}