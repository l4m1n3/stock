<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
   
    protected $fillable = ['title', 'amount', 'type', 'status','payment_method', 'expense_date','branch_id','user_id'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }  
      public function user()
    {
        return $this->belongsTo(User::class);
    } 
}
