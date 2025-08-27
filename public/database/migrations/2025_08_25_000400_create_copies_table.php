<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('code', 50)->unique();
            $table->enum('status', ['available', 'loaned', 'reserved', 'lost'])->default('available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('copies');
    }
};
