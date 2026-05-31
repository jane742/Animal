<?php

namespace Database\Seeders;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


User::create([
    'name' => 'Олексій Коваленко',
    'email' => 'oleksiy@test.com',
    'password' => Hash::make('1111'), // 🔥 Laravel сам захешує!
]);
    }
}
