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
        'meter_reader',
        'syncStatus',
        'sms_sent',
        'sms_sent_at'
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
    const PENALTY_AMOUNT = 20.00;

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

    public function calculatePenalty()
    {
        return self::PENALTY_AMOUNT;
    }

    public function billPayments()
    {
        return $this->hasOne(ConsBillPay::class, 'consread_id', 'consread_id');
    }

    public function getBillAmount()
    {
        return $this->calculateBill();
    }

    public function getReadingDetails($consreadId)
    {
        try {
            $reading = ConsumerReading::with(['consumer'])->findOrFail($consreadId);
            
            if (!$reading || !$reading->consumer) {
                throw new \Exception('Reading or consumer details not found');
            }

            $coverageDate = Cov_date::where('covdate_id', $reading->covdate_id)->first();
            if (!$coverageDate) {
                throw new \Exception('Coverage date not found');
            }

            $lastMonthUnpaidBill = ConsumerReading::with(['billPayments'])
                ->where('customer_id', $reading->customer_id)
                ->where('consread_id', '<', $reading->consread_id)
                ->whereHas('billPayments', function($q) {
                    $q->where('bill_status', 'unpaid');
                })
                ->orderBy('reading_date', 'desc')
                ->first();

            $consumption = $this->calculateConsumption();
            $currentBillAmount = $this->calculateBill();

            $lastMonthBillData = null;
            $totalCombinedAmount = $currentBillAmount;
            $penaltyAmount = 0;

            if ($lastMonthUnpaidBill) {
                $lastMonthAmount = $lastMonthUnpaidBill->calculateBill();
                $penaltyAmount = $this->calculatePenalty();
                $totalCombinedAmount += $lastMonthAmount + $penaltyAmount;
                
                $lastMonthBillData = [
                    'reading_date' => $lastMonthUnpaidBill->reading_date,
                    'due_date' => $lastMonthUnpaidBill->due_date,
                    'consumption' => $lastMonthUnpaidBill->calculateConsumption(),
                    'total_amount' => $lastMonthAmount,
                    'penalty_amount' => $penaltyAmount,
                    'bill_status' => $lastMonthUnpaidBill->billPayments->bill_status
                ];
            }

            return [
                'current_bill_amount' => $currentBillAmount,
                'last_month_unpaid' => $lastMonthBillData,
                'penalty_amount' => $penaltyAmount,
                'total_amount' => $totalCombinedAmount
            ];
        } catch (\Exception $e) {
            Log::error('Error in getReadingDetails: ' . $e->getMessage());
            throw $e;
        }
    }
}
