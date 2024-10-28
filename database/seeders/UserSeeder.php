<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'id' => 1,
            'name' => 'Super Admin',
            'email' => 'admin@gmail.com',
            'role_id' => 1,
            'password' => bcrypt('123456789'),
        ]);
        User::create([
            'id' => 2,
            'name' => 'Training Center',
            'email' => 'training@gmail.com',
            'role_id' => 2,
            'password' => bcrypt('123456789'),
        ]);
        User::create([
            'id' => 3,
            'name' => 'Medical Center',
            'email' => 'medical@gmail.com',
            'role_id' => 3,
            'password' => bcrypt('123456789'),
        ]);
        User::create([
            'id' => 4,
            'name' => 'Agent',
            'email' => 'agent@gmail.com',
            'role_id' => 4,
            'password' => bcrypt('123456789'),
        ]);
        User::create([
            'id' => 5,
            'name' => 'User',
            'email' => 'user@gmail.com',
            'role_id' => 5,
            'password' => bcrypt('123456789'),
        ]);
    }
}
