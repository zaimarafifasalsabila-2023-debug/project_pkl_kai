<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TargetVolumeMuat extends Model
{
    protected $table = 'target_volume_muat';

    protected $fillable = [
        'tahun_program',
        'bulan',
        'target_kg',
    ];
}
