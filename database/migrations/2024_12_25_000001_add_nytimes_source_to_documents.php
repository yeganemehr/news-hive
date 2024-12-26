<?php

use App\Enums\DocumentSource;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $cases = DocumentSource::cases();
            $table->enum('source_type', array_column($cases, 'value'))->change();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $cases = array_diff(DocumentSource::cases(), DocumentSource::NYTIMES);
            $table->enum('source_type', array_column($cases, 'value'))->change();
        });
    }
};
