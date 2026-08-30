<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['pengguna', 'petugas', 'admin'])->default('pengguna')->after('password');
            $table->enum('account_status', ['pending', 'aktif', 'ditolak'])->default('pending')->after('role');
            $table->string('identity', 30)->nullable()->after('account_status');
            $table->string('phone', 20)->nullable()->after('identity');

            $table->index(['role', 'account_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'account_status']);
            $table->dropColumn(['role', 'account_status', 'identity', 'phone']);
        });
    }
};
