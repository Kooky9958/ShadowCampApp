<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccountsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('accounts')->insert([
            [
                'id' => 8189,
                'name' => 'Brian Eno',
                'email' => '15august@southinc.co.nz',
                'ig_user' => '15august',
                'fb_user' => '15august',
                'referrals_code' => null,
                'products' => null,
                'questionnaire' => null,
                'begining' => Carbon::create('2024-08-15 13:07:46'),
                'industry_occupation' => null,
                'declared_healthy' => 0,
                'accepted_tc' => 0,
                'audience' => null,
                'communication' => null,
                'user_id' => 8027,
                'created_at' => Carbon::create('2024-08-15 13:07:46'),
                'updated_at' => Carbon::create('2024-08-19 14:33:58'),
                'stripe_id' => null,
                'pm_type' => null,
                'pm_last_four' => null,
                'trial_ends_at' => null,
                'products_subscribed' => json_encode([]),
                'products_subscribed_override' => json_encode(['camp_delta_migrate' => ['start_date' => '2024-08-19 00:00:00']]),
                'identity_verification' => null,
                'override_general' => json_encode([])
            ],
            [
                'id' => 8191,
                'name' => 'Dan Roberts',
                'email' => '16august@southinc.co.nz',
                'ig_user' => '16august',
                'fb_user' => '16august',
                'referrals_code' => null,
                'products' => null,
                'questionnaire' => null,
                'begining' => Carbon::create('2024-08-16 10:44:08'),
                'industry_occupation' => null,
                'declared_healthy' => 0,
                'accepted_tc' => 0,
                'audience' => null,
                'communication' => null,
                'user_id' => 8029,
                'created_at' => Carbon::create('2024-08-16 10:44:08'),
                'updated_at' => Carbon::create('2024-08-19 14:34:02'),
                'stripe_id' => null,
                'pm_type' => null,
                'pm_last_four' => null,
                'trial_ends_at' => null,
                'products_subscribed' => json_encode([]),
                'products_subscribed_override' => json_encode(['camp_precall' => ['start_date' => '2024-08-19 00:00:00']]),
                'identity_verification' => null,
                'override_general' => json_encode([])
            ]
        ]);
    }
}
