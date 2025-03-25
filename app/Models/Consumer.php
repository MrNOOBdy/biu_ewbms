<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consumer extends Model
{
    protected $table = 'water_consumers';
    protected $primaryKey = 'watercon_id';

    protected $fillable = [
        'block_id',
        'customer_id',
        'firstname',
        'middlename',
        'lastname',
        'address',
        'contact_no',
        'consumer_type',
        'status',
        'application_fee',
        'service_fee'
    ];

    public function block()
    {
        return $this->belongsTo(Block::class, 'block_id', 'block_id');
    }

    public function readings()
    {
        return $this->hasMany(ConsumerReading::class, 'customer_id', 'customer_id');
    }

    public function connectionPayments()
    {
        return $this->hasMany(ConnPayment::class, 'customer_id', 'customer_id');
    }

    public function serviceFeePayments()
    {
        return $this->hasMany(ServiceFee::class, 'customer_id', 'customer_id');
    }

    public function billRate()
    {
        return $this->belongsTo(Bill_rate::class, 'consumer_type', 'consumer_type');
    }
}
