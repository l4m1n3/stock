<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    // $table->id();
    //             $table->string('name'); // Niamey, Maradi, Zinder
    //             $table->string('city')->nullable();
    //             $table->timestamps();
    protected $fillable = ['name', 'city'];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
    public function confections()
    {
        return $this->hasMany(Confection::class);
    }
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
