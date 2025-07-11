<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar usuários de teste não aprovados
        $testUsers = [
            [
                'name' => 'João Silva',
                'email' => 'joao@teste.com',
                'password' => Hash::make('password'),
                'is_approved' => false,
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria@teste.com',
                'password' => Hash::make('password'),
                'is_approved' => false,
            ],
            [
                'name' => 'Pedro Oliveira',
                'email' => 'pedro@teste.com',
                'password' => Hash::make('password'),
                'is_approved' => false,
            ],
        ];

        foreach ($testUsers as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
