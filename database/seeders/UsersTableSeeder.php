<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'id' => 8027,
                'name' => 'Kate Moss',
                'email' => '15august@southinc.co.nz',
                'gender' => 'Female',
                'age' => 36,
                'height' => 6.00,
                'weight' => 35.00,
                'address_line1' => 'sdsd',
                'city' => 'Cortina',
                'region' => 'Veneto',
                'country' => 'Italy',
                'postcode' => '395004',
                'hobbies' => json_encode(['Sports', 'Music', 'Test', 'Books']),
                'email_verified_at' => Carbon::create('2024-08-15 13:12:44'),
                'password' => '$2y$12$rtYw83TiZGMhvADY8pgpqOE4qlQQT27ed6BknO663VJoXQumOwKLC',
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
                'remember_token' => 'AvQgS9LG8q3WxPGsENkrW636iBscXTl70Xo4bykTAbASmY6LA3uS7h94k6yS',
                'current_team_id' => null,
                'profile_photo_path' => 'profile_photos/6oyU8LKFzmlZBDk0JM7rPgqq9fbnbtXYAmFkA4fQ.jpg',
                'created_at' => Carbon::create('2024-08-15 13:07:46'),
                'updated_at' => Carbon::create('2024-10-10 14:26:08'),
                'privileges' => null
            ],
            [
                'id' => 8029,
                'name' => 'Dan Roberts',
                'email' => '16august@southinc.co.nz',
                'gender' => 'Female',
                'age' => 36,
                'height' => 50.00,
                'weight' => 55.00,
                'address_line1' => 'test',
                'city' => 'Surat',
                'region' => 'Berat',
                'country' => 'Albania',
                'postcode' => '395004',
                'hobbies' => json_encode(['Sports', 'Test']),
                'email_verified_at' => Carbon::create('2024-08-16 10:46:21'),
                'password' => '$2y$12$9Zb7xmt2TgBdXvpd3LWEQuT.ohbNz/Dmn3m0EFRt6WUNBjFabExe.',
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
                'remember_token' => 'PJlNinTSDnWd8eHgCCLvWqDTuFqnTWWyGLvoDasfCjDH4GugoHlqCcG1UsDC',
                'current_team_id' => null,
                'profile_photo_path' => null,
                'created_at' => Carbon::create('2024-08-16 10:44:08'),
                'updated_at' => Carbon::create('2024-10-04 10:07:35'),
                'privileges' => null
            ]
        ]);
    }
}
