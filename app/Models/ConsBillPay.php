<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsBillPay extends Model
{
    protected $table = 'consumer_bill_pay';
    
    protected $fillable = [
        'consread_id',
        'total_amount',
        'bill_tendered_amount',
        'bill_status'
    ];

    public function consumerReading()
    {
        return $this->belongsTo(ConsumerReading::class, 'consread_id', 'consread_id');
    }
}
