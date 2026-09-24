<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Kampus',
            'email' => 'admin@kampus.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_verified' => true,
        ]);

        User::create([
            'name' => 'Staff Fasilitas 1',
            'email' => 'staff1@kampus.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'is_verified' => true,
        ]);

        User::create([
            'name' => 'Staff Fasilitas 2',
            'email' => 'staff2@kampus.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'is_verified' => true,
        ]);

        $verifiedUsers = [
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad.fauzi@student.ac.id'],
            ['name' => 'Siti Nurhaliza', 'email' => 'siti.nurhaliza@student.ac.id'],
            ['name' => 'Budi Santoso', 'email' => 'budi.santoso@student.ac.id'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi.lestari@student.ac.id'],
            ['name' => 'Eko Prasetyo', 'email' => 'eko.prasetyo@lecturer.ac.id'],
        ];

        foreach ($verifiedUsers as $userData) {
            User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_verified' => true,
            ]);
        }

        $unverifiedUsers = [
            ['name' => 'Rina Wati', 'email' => 'rina.wati@student.ac.id'],
            ['name' => 'Joko Widodo', 'email' => 'joko.widodo@student.ac.id'],
            ['name' => 'Kartika Sari', 'email' => 'kartika.sari@student.ac.id'],
        ];

        foreach ($unverifiedUsers as $userData) {
            User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_verified' => false,
            ]);
        }
    }
}
