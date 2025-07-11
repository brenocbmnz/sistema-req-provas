<?php

use App\Models\User;
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
        // Aprovar o usuário administrador
        User::where('email', 'admin@admin.com')->update([
            'is_approved' => true,
            'approved_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter a aprovação do admin
        User::where('email', 'admin@admin.com')->update([
            'is_approved' => false,
            'approved_at' => null,
        ]);
    }
};
