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
        User::create([
        'name' => 'Owner',
        'email' => 'owner@gym.com',
        'password' => bcrypt('password'),
        'role' => 'owner'
    ]);
    }
}
