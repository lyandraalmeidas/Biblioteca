<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('isbn', 20)->nullable()->unique();
            $table->unsignedSmallInteger('year')->nullable();
            $table->foreignId('author_id')->constrained('authors')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('publisher_id')->nullable()->constrained('publishers')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
