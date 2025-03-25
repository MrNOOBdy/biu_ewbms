<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill_rate extends Model
{
    use HasFactory;

    protected $table = 'bill_rate';
    protected $primaryKey = 'billrate_id';
    public $incrementing = true;

    protected $fillable = [
        'consumer_type',
        'cubic_meter',
        'value',
        'excess_value_per_cubic'
    ];

    public function consumers()
    {
        return $this->hasMany(Consumer::class, 'consumer_type', 'consumer_type');
    }
}
