<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    protected $table = 'stations';

    protected $fillable = [
        'kode_stasiun',
        'nama_ka_stasiun'
    ];

    public function angkutan()
    {
        return $this->hasMany(Angkutan::class);
    }
}