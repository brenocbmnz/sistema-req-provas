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
        Schema::table('requerimentos', function (Blueprint $table) {
            // Remove as foreign keys existentes
            $table->dropForeign(['aluno_id']);
            $table->dropForeign(['trimestre_id']);
            $table->dropForeign(['disciplina_id']);
            $table->dropForeign(['professor_id']);
        });

        Schema::table('requerimentos', function (Blueprint $table) {
            // Recria as foreign keys com restrict ao invés de cascade
            $table->foreign('aluno_id')->references('id')->on('alunos')->restrictOnDelete();
            $table->foreign('trimestre_id')->references('id')->on('trimestres')->restrictOnDelete();
            $table->foreign('disciplina_id')->references('id')->on('disciplinas')->restrictOnDelete();
            $table->foreign('professor_id')->references('id')->on('professors')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requerimentos', function (Blueprint $table) {
            // Remove as foreign keys com restrict
            $table->dropForeign(['aluno_id']);
            $table->dropForeign(['trimestre_id']);
            $table->dropForeign(['disciplina_id']);
            $table->dropForeign(['professor_id']);
        });

        Schema::table('requerimentos', function (Blueprint $table) {
            // Volta para as foreign keys com cascade (comportamento original)
            $table->foreign('aluno_id')->references('id')->on('alunos')->cascadeOnDelete();
            $table->foreign('trimestre_id')->references('id')->on('trimestres')->cascadeOnDelete();
            $table->foreign('disciplina_id')->references('id')->on('disciplinas')->cascadeOnDelete();
            $table->foreign('professor_id')->references('id')->on('professors')->cascadeOnDelete();
        });
    }
};
