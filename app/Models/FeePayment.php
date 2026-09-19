<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeePayment extends Model
{
    protected $fillable = ['fee_record_id', 'amount', 'payment_date', 'payment_mode'];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
        ];
    }

    public function feeRecord()
    {
        return $this->belongsTo(FeeRecord::class);
    }
}