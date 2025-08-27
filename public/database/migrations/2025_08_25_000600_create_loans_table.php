<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('copy_id')->constrained('copies')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('loaned_at')->useCurrent();
            $table->dateTime('due_at');
            $table->dateTime('returned_at')->nullable();
            $table->enum('status', ['open', 'returned', 'late'])->default('open');
            $table->timestamps();
            $table->index(['member_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
