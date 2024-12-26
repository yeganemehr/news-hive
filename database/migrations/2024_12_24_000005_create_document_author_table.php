<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_author', function (Blueprint $table) {
            $table->foreignUlid('document_id')
                ->references('id')
                ->on('documents')
                ->cascadeOnDelete();

            $table->foreignUlid('author_id')
                ->references('id')
                ->on('authors')
                ->cascadeOnDelete();

            $table->primary(['document_id', 'author_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_author');
    }
};
