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
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->enum('type', ['ruang_kelas', 'aula', 'laboratorium', 'alat', 'lapangan']);
            $table->string('location', 120);
            $table->unsignedInteger('capacity');
            $table->text('description')->nullable();
            $table->string('photo')->nullable();
            $table->enum('status', ['aktif', 'perbaikan', 'nonaktif'])->default('aktif');
            $table->timestamps();

            $table->index('type');
            $table->index('location');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
