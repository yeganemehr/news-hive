<?php

use App\Enums\DocumentSource;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('image');
            $table->longText('content');

            // I could just use string datatype to easy add/delete sources but enum datatype has serious optimization in most RDBMSes.
            // Its a trade-off
            $table->enum('source_type', array_column(DocumentSource::cases(), 'value'));
            $table->string('source_id');
            $table->timestamps();
            $table->timestamp('published_at')->nullable()->index();

            // The order of columns in indexes affects performance!
            $table->unique(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
