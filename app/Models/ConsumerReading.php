<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ConsumerReading extends Model
{
    protected $table = 'consumer_reading';
    protected $primaryKey = 'consread_id';

    protected $fillable = [
        'customer_id',
        'covdate_id',
        'reading_date',
        'due_date',
        'previous_reading',
        'present_reading',
        'consumption',
        'meter_reader'
    ];

    public function consumer()
    {
        return $this->belongsTo(Consumer::class, 'customer_id', 'customer_id');
    }

    public function coverageDate()
    {
        return $this->belongsTo(CoverageDate::class, 'covdate_id', 'covdate_id');
    }

    const BASE_CUBIC_LIMIT = 10;

    public function calculateConsumption()
    {
        return $this->present_reading - $this->previous_reading;
    }

    public function getBillRate()
    {
        return Bill_rate::where('consumer_type', $this->consumer->consumer_type)
                       ->where('cubic_meter', self::BASE_CUBIC_LIMIT)
                       ->first();
    }

    public function calculateBill()
    {
        try {
            $consumption = $this->calculateConsumption();
            $billRate = $this->getBillRate();
            
            if (!$billRate) {
                Log::error('Bill rate not found for consumer type: ' . $this->consumer->consumer_type);
                return 0;
            }

            $baseCharge = $billRate->value;

            if ($consumption <= self::BASE_CUBIC_LIMIT) {
                return (float)$baseCharge;
            }

            $excessCubic = $consumption - self::BASE_CUBIC_LIMIT;
            $excessCharge = $excessCubic * (float)$billRate->excess_value_per_cubic;

            return (float)$baseCharge + $excessCharge;
        } catch (\Exception $e) {
            Log::error('Error calculating bill: ' . $e->getMessage());
            return 0;
        }
    }

    public function billPayments()
    {
        return $this->hasOne(ConsBillPay::class, 'consread_id', 'consread_id');
    }

    public function getBillAmount()
    {
        return $this->calculateBill();
    }
}
