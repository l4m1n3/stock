<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
   
    protected $fillable = ['title', 'amount', 'type', 'expense_date','branch_id'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }   
}
