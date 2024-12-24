<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('subscription_products')->insert([
            'name' => 'Delta Camp',
            'machine_name' => 'camp_delta',
            'description' => 'Shadow Camp Delta',
            'billing_period' => '30',
            'billing_amount' => '160',
            'active' => true,
            'date_start' => date('Y-m-d H:i:s')
        ]);

        DB::table('subscription_products')->insert([
            'name' => 'Precall Camp',
            'machine_name' => 'camp_precall',
            'description' => 'Shadow Camp Precall',
            'billing_period' => '0',
            'billing_amount' => '140',
            'active' => true,
            'date_start' => date('Y-m-d H:i:s')
        ]);

        DB::table('video_live_streams')->insert([
            'url_id' => \App\Helpers\CustomHelper::generateUrlId('Delta Live Stream'),
            'name' => 'Delta Live Stream',
            'description' => 'Delta Live Stream',
            'audience' => json_encode(['delta']),
            'published' => true,
            'cdn_vendor' => 'cloudflare',
            'date_created' => date('Y-m-d H:i:s'),
            'cdn_id' => '46ffed4fcea6e98d52609409b399e5bd'
        ]);

        // Register the CountrySeeder
        $this->call(CountrySeeder::class);

        // Register the RegionSeeder
        $this->call(RegionSeeder::class);

        $this->call(UsersTableSeeder::class);

        $this->call(AccountsTableSeeder::class);
    }
}
