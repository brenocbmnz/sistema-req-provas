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
            $table->string('nome_completo')->after('aluno_id');
            $table->string('nivel_ensino')->after('nome_completo');
            $table->integer('ano')->after('nivel_ensino');
            $table->string('turma', 1)->after('ano');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requerimentos', function (Blueprint $table) {
            $table->dropColumn(['nome_completo', 'nivel_ensino', 'ano', 'turma']);
        });
    }
};
