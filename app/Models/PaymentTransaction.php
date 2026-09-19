<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = ['merchant_txn_no', 'fee_record_id', 'amount', 'status'];

    public function feeRecord()
    {
        return $this->belongsTo(FeeRecord::class);
    }
}