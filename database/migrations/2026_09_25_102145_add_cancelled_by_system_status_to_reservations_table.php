<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan status kadaluarsa untuk reservasi pending yang melewati
     * batas persetujuan (BR-3, lead time 60 menit sebelum start_time).
     */
    public function up(): void
    {
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('reservations')->where('status', 'cancelled_by_system')
            ->update(['status' => 'cancelled_by_officer']);

        Schema::table('reservations', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'cancelled_by_user',
                'cancelled_by_officer',
            ])->default('pending')->change();
        });
    }
};
