<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'category', 'serial_number', 'is_active'])]
class Equipment extends Model
{
    public function loans() {
        return $this->hasMany(Loan::class);
    }
}
