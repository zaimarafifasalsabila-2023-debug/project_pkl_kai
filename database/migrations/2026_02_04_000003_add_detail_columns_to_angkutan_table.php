<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('angkutan')) {
            return;
        }

        Schema::table('angkutan', function (Blueprint $table) {
            if (!Schema::hasColumn('angkutan', 'nomor_sa')) {
                $table->string('nomor_sa')->nullable()->after('status_sa');
            }
            if (!Schema::hasColumn('angkutan', 'tanggal_pembuatan_sa')) {
                $table->date('tanggal_pembuatan_sa')->nullable()->after('nomor_sa');
            }
            if (!Schema::hasColumn('angkutan', 'tanggal_sa')) {
                $table->date('tanggal_sa')->nullable()->after('tanggal_pembuatan_sa');
            }
            if (!Schema::hasColumn('angkutan', 'jenis_hari_operasi')) {
                $table->string('jenis_hari_operasi')->nullable()->after('tanggal_sa');
            }
            if (!Schema::hasColumn('angkutan', 'nomor_manifest')) {
                $table->string('nomor_manifest')->nullable()->after('jenis_hari_operasi');
            }
            if (!Schema::hasColumn('angkutan', 'komoditi')) {
                $table->string('komoditi')->nullable()->after('nomor_manifest');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('angkutan')) {
            return;
        }

        Schema::table('angkutan', function (Blueprint $table) {
            $cols = [
                'nomor_sa',
                'tanggal_pembuatan_sa',
                'tanggal_sa',
                'jenis_hari_operasi',
                'nomor_manifest',
                'komoditi',
            ];

            foreach ($cols as $c) {
                if (Schema::hasColumn('angkutan', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
