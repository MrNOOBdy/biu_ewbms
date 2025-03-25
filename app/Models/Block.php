<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    protected $table = 'blocks';
    protected $primaryKey = 'block_id';
    protected $fillable = ['block_id', 'barangays'];

    protected $casts = [
        'barangays' => 'array'
    ];
}
