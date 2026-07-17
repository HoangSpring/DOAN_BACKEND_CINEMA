<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1 Admin
        User::create([
            'full_name' => 'Admin Hệ Thống',
            'email' => 'admin@cinema.vn',
            'phone' => '0900000000',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2 Staff
        User::create([
            'full_name' => 'Nguyễn Văn Nhân Viên',
            'email' => 'staff@cinema.vn',
            'phone' => '0900000001',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);
        User::create([
            'full_name' => 'Trần Thị Nhân Viên 2',
            'email' => 'staff2@cinema.vn',
            'phone' => '0900000002',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);

        // 30 Customer
        User::factory()->count(30)->create(['role' => 'customer']);
    }
}
