<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Angkutan extends Model
{
    protected $table = 'angkutan';

    protected $fillable = [
        'customer_id',
        'station_id',
        'tanggal_keberangkatan',
        'nomor_sarana',
        'tonase',
        'koli',
        'sumber_file'
    ];

    protected $casts = [
        'tanggal_keberangkatan' => 'date'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}