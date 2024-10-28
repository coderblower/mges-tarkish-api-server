<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'id' => 1,
            'roleName' => 'Admin',
        ]);
        Role::create([
            'id' => 2,
            'roleName' => 'Training Center',
        ]);
        Role::create([
            'id' => 3,
            'roleName' => 'Medical Center',
        ]);
        Role::create([
            'id' => 4,
            'roleName' => 'Agent',
        ]);
        Role::create([
            'id' => 5,
            'roleName' => 'User',
        ]);
    }
}
