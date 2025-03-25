<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fees extends Model
{
    protected $primaryKey = 'fee_id';
    protected $fillable = ['fee_type', 'amount'];
}
