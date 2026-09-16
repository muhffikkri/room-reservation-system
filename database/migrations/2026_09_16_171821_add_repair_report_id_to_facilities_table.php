<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table): void {
            $table->foreignId('repair_report_id')
                ->nullable()
                ->after('status')
                ->constrained('reports')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table): void {
            $table->dropForeign(['repair_report_id']);
            $table->dropColumn('repair_report_id');
        });
    }
};
