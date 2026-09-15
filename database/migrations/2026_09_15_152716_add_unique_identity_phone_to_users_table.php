<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Spec §4.1: identity dan phone wajib dan unik. NOT NULL ditulis via
     * statement agar tidak butuh doctrine/dbal; unique via Schema Builder.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `users` MODIFY `identity` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL');
        DB::statement('ALTER TABLE `users` MODIFY `phone` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->unique('identity');
            $table->unique('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['identity']);
            $table->dropUnique(['phone']);
        });

        DB::statement('ALTER TABLE `users` MODIFY `identity` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');
        DB::statement('ALTER TABLE `users` MODIFY `phone` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');
    }
};
