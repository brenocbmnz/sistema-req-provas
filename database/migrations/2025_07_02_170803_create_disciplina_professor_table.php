<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
Schema::create('disciplina_professor', function (Blueprint $table) {
    $table->foreignId('disciplina_id')->constrained()->cascadeOnDelete();
    $table->foreignId('professor_id')->constrained()->cascadeOnDelete();
    $table->primary(['disciplina_id', 'professor_id']);
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disciplina_professor');
    }
};
