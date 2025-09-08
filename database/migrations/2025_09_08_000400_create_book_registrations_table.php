<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('book_registrations', function (Blueprint $table) {
            $table->id();
            // Reference to an existing book when the registration is for a known book
            $table->foreignId('book_id')->nullable()->constrained('books')->cascadeOnUpdate()->nullOnDelete();

            // Allow storing a title directly in the registration (for new/uncatalogued books)
            $table->string('title', 200)->nullable();

            // Optional references to metadata
            $table->foreignId('author_id')->nullable()->constrained('authors')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('publisher_id')->nullable()->constrained('publishers')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->cascadeOnUpdate()->nullOnDelete();

            $table->string('isbn', 20)->nullable()->index();

            // Who registered the book (user id)
            $table->foreignId('registered_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();

            // When the registration happened
            $table->timestamp('registered_at')->useCurrent();

            // Any notes about the registration (condition, source, etc.)
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_registrations');
    }
};
