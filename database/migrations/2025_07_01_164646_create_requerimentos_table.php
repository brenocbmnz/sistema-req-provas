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
Schema::create('requerimentos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
    $table->foreignId('trimestre_id')->constrained('trimestres')->cascadeOnDelete();
    $table->foreignId('disciplina_id')->constrained('disciplinas')->cascadeOnDelete();
    $table->date('data_requerimento');
    $table->text('motivo');
    $table->string('status')->default('Pendente'); // Pendente, Aprovado, Reprovado
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requerimentos');
    }
};
