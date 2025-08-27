<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('copy_id')->constrained('copies')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnUpdate()->cascadeOnDelete();
            $table->dateTime('reserved_at')->useCurrent();
            $table->enum('status', ['active', 'fulfilled', 'cancelled'])->default('active');
            $table->timestamps();
            $table->unique(['copy_id', 'member_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
