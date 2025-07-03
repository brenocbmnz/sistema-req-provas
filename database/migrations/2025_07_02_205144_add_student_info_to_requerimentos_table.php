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
            // Adicionar informações do aluno
            $table->string('nome_completo');
            $table->string('nivel_ensino');
            $table->string('ano');
            $table->string('turma');
            
            // Adicionar outras colunas necessárias
            $table->foreignId('professor_id')->nullable()->constrained('professors')->cascadeOnDelete();
            $table->text('observacao')->nullable();
            
            // Tornar aluno_id opcional, já que agora armazenaremos as informações diretamente
            $table->foreignId('aluno_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requerimentos', function (Blueprint $table) {
            $table->dropColumn(['nome_completo', 'nivel_ensino', 'ano', 'turma', 'observacao']);
            $table->dropForeign(['professor_id']);
            $table->dropColumn('professor_id');
            
            // Reverter aluno_id para não ser nullable
            $table->foreignId('aluno_id')->nullable(false)->change();
        });
    }
};
