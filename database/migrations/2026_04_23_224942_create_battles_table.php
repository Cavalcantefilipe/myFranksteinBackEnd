<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('battles', function (Blueprint $table) {
            $table->id();
            $table->jsonb('blue_team');
            $table->jsonb('red_team');
            $table->string('winner', 10)->nullable();
            $table->integer('turns')->default(0);
            $table->jsonb('log');
            $table->timestamps();

            $table->index('created_at');
            $table->index('winner');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('battles');
    }
};
