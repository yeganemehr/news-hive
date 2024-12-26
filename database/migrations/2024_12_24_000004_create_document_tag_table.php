<?php

use App\Enums\TagRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_tag', function (Blueprint $table) {
            $table->foreignUlid('document_id')
                ->references('id')->on('documents')
                ->cascadeOnDelete();
            $table->foreignUlid('tag_id')
                ->references('id')
                ->on('tags')
                ->cascadeOnDelete();
            $table->enum('role', array_column(TagRole::cases(), 'name'))->index();

            $table->primary(['document_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_tag');
    }
};
