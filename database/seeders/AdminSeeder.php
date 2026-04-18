<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'nazrinkyushi10@gmail.com'],
            [
                'name'      => 'nazrin',
                'username'  => 'nazrin',
                'email'     => 'nazrinkyushi10@gmail.com',
                'password'  => Hash::make('1100'),
                'role'      => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'operator@radius.local'],
            [
                'name'      => 'sukmo',
                'username'  => 'sukmo',
                'email'     => 'sukmo@radius.local',
                'password'  => Hash::make('9090'),
                'role'      => 'operator',
                'is_active' => true,
            ]
        );
    }
}
