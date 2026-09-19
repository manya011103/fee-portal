<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DueDateHistory extends Model
{
    protected $fillable = ['student_id', 'old_due_date', 'new_due_date', 'changed_by'];

    protected function casts(): array
    {
        return [
            'old_due_date' => 'date',
            'new_due_date' => 'date',
        ];
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}