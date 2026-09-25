<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan status penolakan otomatis untuk reservasi pending yang
     * bertabrakan dengan reservasi lain yang disetujui petugas (overlap).
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'rejected_by_system',
                'cancelled_by_user',
                'cancelled_by_officer',
                'cancelled_by_system',
            ])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('reservations')->where('status', 'rejected_by_system')
            ->update(['status' => 'rejected']);

        Schema::table('reservations', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'cancelled_by_user',
                'cancelled_by_officer',
                'cancelled_by_system',
            ])->default('pending')->change();
        });
    }
};
