<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cov_date extends Model
{
    protected $table = 'coverage_date';
    protected $primaryKey = 'covdate_id';
    
    protected $fillable = [
        'coverage_date_from',
        'coverage_date_to',
        'reading_date',
        'due_date',
        'status'
    ];

    // Updated constants to match migration
    const STATUS_OPEN = 'Open';
    const STATUS_CLOSE = 'Close';
}
