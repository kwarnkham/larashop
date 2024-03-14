<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        DB::table('roles')->insert([
            [
                'name' => 'admin',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ]);

        DB::table('users')->insert([
            [
                'name' => 'admin',
                'email' => 'admin@larashop.com',
                'password' => bcrypt('password'),
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);

        DB::table('role_user')->insert([
            [
                'user_id' => DB::table('users')->first()->id,
                'role_id' => DB::table('roles')->first()->id,
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);
    }
}
