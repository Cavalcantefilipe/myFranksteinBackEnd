<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translation_cache', function (Blueprint $table) {
            $table->id();
            $table->text('source_text');
            $table->string('source_lang', 10)->nullable();
            $table->string('target_lang', 10);
            $table->text('translated_text');
            $table->timestamps();

            $table->index(['source_lang', 'target_lang']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_cache');
    }
};
