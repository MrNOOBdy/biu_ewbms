<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeterReaderBlock extends Model
{
    protected $fillable = ['user_id', 'block_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function block()
    {
        return $this->belongsTo(Block::class, 'block_id', 'block_id');
    }
}
