<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requerimentos', function (Blueprint $table) {
            // Adiciona a coluna 'observacao' depois da coluna 'motivo'
            $table->text('observacao')->nullable()->after('motivo');
        });
    }

    public function down(): void
    {
        Schema::table('requerimentos', function (Blueprint $table) {
            $table->dropColumn('observacao');
        });
    }
};
