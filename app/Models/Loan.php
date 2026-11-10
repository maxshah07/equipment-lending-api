<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'due_at', 'returned_at'])]
class Loan extends Model
{
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function equipment() {
        return $this->belongsTo(Equipment::class);
    }
}
