<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Angkutan extends Model
{
    protected $table = 'angkutan';

    protected $fillable = [
        'jenis_angkutan',
        'nama_customer',
        'stasiun_asal_sa',
        'stasiun_tujuan_sa',
        'nama_ka_stasiun_asal',
        'tanggal_keberangkatan_asal_ka',
        'nomor_sarana',
        'volume_berat_kai',
        'banyaknya_pengajuan',
        'status_sa'
    ];

    protected $casts = [
        'tanggal_keberangkatan_asal_ka' => 'date',
        'volume_berat_kai' => 'decimal:2'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'nama_customer', 'nama_customer');
    }
}