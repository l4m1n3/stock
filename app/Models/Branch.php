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
}
