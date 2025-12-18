<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = [
            [
                'name'  => 'Jarric',
                'email' => 'jarric.creencia@astoria.com.ph',
                'password' => 'a04372337571',
                'role' => 'Admin', // remove if you don't have a "role" column
            ],
            [
                'name'  => 'Jayson',
                'email' => 'digital.tech@astoria.com.ph',
                'password' => 'QwertY12345!!',
                'role' => 'Admin', // remove if you don't have a "role" column
            ],
        ];

        foreach ($admins as $data) {
            User::updateOrCreate(
                ['email' => $data['email']], // find by email
                [
                    'name'     => $data['name'],
                    'password' => Hash::make($data['password']),
                    // comment this out if your users table has no "role" column
                    'role'     => $data['role'] ?? null,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
