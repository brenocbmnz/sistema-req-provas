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
            // Remove a foreign key constraint
            $table->dropForeign(['aluno_id']);
            
            // Torna o campo aluno_id nullable
            $table->foreignId('aluno_id')->nullable()->change();
            
            // Recria a foreign key constraint
            $table->foreign('aluno_id')->references('id')->on('alunos')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requerimentos', function (Blueprint $table) {
            // Remove a foreign key constraint
            $table->dropForeign(['aluno_id']);
            
            // Volta o campo aluno_id para NOT NULL
            $table->foreignId('aluno_id')->nullable(false)->change();
            
            // Recria a foreign key constraint
            $table->foreign('aluno_id')->references('id')->on('alunos')->restrictOnDelete();
        });
    }
};
