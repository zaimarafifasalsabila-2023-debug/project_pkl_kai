<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'nama_customer'
    ];

    public function angkutan()
    {
        return $this->hasMany(Angkutan::class);
    }
}