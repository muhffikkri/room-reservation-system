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
        Schema::create('account_verification_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->enum('action', ['verified', 'rejected', 'restored']);
            $table->timestamp('acted_at')->useCurrent();

            $table->index(['target_user_id', 'acted_at']);
            $table->index(['actor_id', 'acted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_verification_actions');
    }
};
