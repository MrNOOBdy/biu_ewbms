<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConnPayment extends Model
{
    protected $table = 'conn_payment';
    protected $primaryKey = 'connpay_id';
    
    protected $fillable = [
        'customer_id',
        'application_fee',
        'conn_amount_paid',
        'conn_pay_status',
    ];

    protected $casts = [
        'application_fee' => 'decimal:2',
        'conn_amount_paid' => 'decimal:2',
        'conn_pay_status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'conn_amount_paid' => 0.00,
        'conn_pay_status' => 'unpaid'
    ];

    public function consumer()
    {
        return $this->belongsTo(Consumer::class, 'customer_id', 'customer_id');
    }
}
