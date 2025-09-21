<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reading_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('book_id');
            $table->date('date');
            $table->unsignedInteger('seconds')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'book_id', 'date'], 'reading_stats_user_book_date_unique');

            // Optional FKs if tables exist
            // $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            // $table->foreign('book_id')->references('id')->on('books')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_stats');
    }
};
