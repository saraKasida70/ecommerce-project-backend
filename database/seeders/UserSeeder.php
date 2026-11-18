<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Test Customer',
                'email' => 'customer@test.com',
                'password' => bcrypt('password123'),
                'role' => 'customer',
            ],
            [
                'name' => 'Test Admin',
                'email' => 'admin@test.com',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                $user
            );
        }

        $this->command->info('Created test users');
    }
}
