<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeReminderQueue extends Model
{
    protected $table = 'fee_reminder_queue';
    protected $fillable = ['mobile'];
    public $timestamps = false;
}