<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['username' => 'admin',   'password' => Hash::make('123'), 'role' => 'admin'],
            ['username' => 'kasir',   'password' => Hash::make('123'), 'role' => 'kasir'],
            ['username' => 'manager', 'password' => Hash::make('123'), 'role' => 'manager'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['username' => $user['username']],
                $user
            );
        }
    }
}
