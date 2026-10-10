<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@kampus.ac.id'],
            [
                'name' => 'Admin Kampus',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_verified' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'staff1@kampus.ac.id'],
            [
                'name' => 'Staff Fasilitas 1',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'is_verified' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'staff2@kampus.ac.id'],
            [
                'name' => 'Staff Fasilitas 2',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'is_verified' => true,
            ]
        );

        $verifiedUsers = [
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad.fauzi@student.ac.id'],
            ['name' => 'Siti Nurhaliza', 'email' => 'siti.nurhaliza@student.ac.id'],
            ['name' => 'Budi Santoso', 'email' => 'budi.santoso@student.ac.id'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi.lestari@student.ac.id'],
            ['name' => 'Eko Prasetyo', 'email' => 'eko.prasetyo@lecturer.ac.id'],
        ];

        foreach ($verifiedUsers as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'user',
                    'is_verified' => true,
                ]
            );
        }

        $unverifiedUsers = [
            ['name' => 'Rina Wati', 'email' => 'rina.wati@student.ac.id'],
            ['name' => 'Joko Widodo', 'email' => 'joko.widodo@student.ac.id'],
            ['name' => 'Kartika Sari', 'email' => 'kartika.sari@student.ac.id'],
        ];

        foreach ($unverifiedUsers as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'user',
                    'is_verified' => false,
                ]
            );
        }
    }
}
