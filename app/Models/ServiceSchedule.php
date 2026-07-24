<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSchedule extends Model
{
    protected $fillable = [
        'service_id',
        'sale_id',
        'client_name',
        'contact_number',
        'address',
        'scheduled_date',
        'scheduled_time',
        'status',
        'notes',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}