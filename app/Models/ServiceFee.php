<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceFee extends Model
{
    protected $table = 'service_fee_payment';
    protected $primaryKey = 'service_pay_id';
    public $timestamps = true;
    
    protected $fillable = [
        'customer_id',
        'service_amount_paid',
        'reconnection_fee',
        'service_paid_status',
    ];

    protected $casts = [
        'service_amount_paid' => 'decimal:2',
        'reconnection_fee' => 'decimal:2',
        'service_paid_status' => 'string',
    ];

    public function consumer()
    {
        return $this->belongsTo(Consumer::class, 'customer_id', 'customer_id');
    }
}
