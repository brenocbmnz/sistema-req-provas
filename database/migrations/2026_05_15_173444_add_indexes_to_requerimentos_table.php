<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requerimentos', function (Blueprint $table) {
            $table->index('nome_completo');
            $table->index('status');
            $table->index('data_requerimento');
        });
    }

    public function down(): void
    {
        Schema::table('requerimentos', function (Blueprint $table) {
            $table->dropIndex(['nome_completo']);
            $table->dropIndex(['status']);
            $table->dropIndex(['data_requerimento']);
        });
    }
};
