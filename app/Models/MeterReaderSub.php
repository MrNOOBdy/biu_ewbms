<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MeterReaderSub extends Model
{
    protected $table = 'meter_reader_substitutions';
    
    public $timestamps = true;

    protected $fillable = [
        'absent_reader_id',
        'substitute_reader_id',
        'start_date',
        'end_date',
        'status',
        'reason'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function absentReader()
    {
        return $this->belongsTo(User::class, 'absent_reader_id', 'user_id');
    }

    public function substituteReader()
    {
        return $this->belongsTo(User::class, 'substitute_reader_id', 'user_id');
    }

    public function isActive($date = null)
    {
        $date = $date ?: Carbon::now();
        return $this->status === 'active' &&
               $date->between($this->start_date, $this->end_date);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('start_date', '<=', Carbon::now())
                    ->where('end_date', '>=', Carbon::now());
    }

    public function getAssignedBlocks()
    {
        return MeterReaderBlock::where('user_id', $this->absent_reader_id)->get();
    }
}
