<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    public function patient():BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
    public function company():BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function billing():BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }

    protected static function booted(): void
    {

        $updateBillingStatus = function (Payment $payment) {

            if ($payment->billing) {

                $totalPayments = $payment->billing->payments()->sum('amount');

                $billingTotal = $payment->billing->total;

                if (abs($totalPayments - $billingTotal) < 0.0001) {

                    $payment->billing->status = 'Paid';

                    self::extracted($payment);


                } else {

                    if ($totalPayments > 0) {

                        $payment->billing->status = 'Processing';

                        self::extracted($payment);

                    } else {

                        $payment->billing->status = 'Pending';

                    }

                }


                $payment->billing->save();

            }

        };


        static::created($updateBillingStatus);
        static::updated($updateBillingStatus);

        static::deleted($updateBillingStatus);
    }
    protected static function extracted(Payment $payment): void
    {
        $billingType = $payment->billing->type;

        $commonData = [
            'patient_id' => $payment->billing->patient_id,
            'billing_id' => $payment->billing->id,
            'company_id' => $payment->billing->company_id,
        ];

        $matchCriteria = [
            'billing_id' => $payment->billing->id,
        ];

        if ($billingType === 'Laboratory') {
            $lab = Laboratory::updateOrCreate($matchCriteria, $commonData);
            if($lab){
                LineItem::query()
                    ->where('billing_id', $payment->billing->id)
                    ->update([
                        'company_id' => $payment->billing->company_id,
                        'patient_id' => $payment->billing->patient_id,
                        'laboratory_id' => $lab->id
                    ]);
            }

        } elseif ($billingType === 'Scanning') {
            $scan = Scanning::updateOrCreate($matchCriteria, $commonData);
            if($scan){
                LineItem::query()
                    ->where('billing_id', $payment->billing->id)
                    ->update([
                        'company_id' => $payment->billing->company_id,
                        'patient_id' => $payment->billing->patient_id,
                        'scanning_id' => $scan->id
                    ]);
            }
        }
    }
}
