<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MoodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $moods = [
            ['name' => 'Mood swings', 'icon' => '🌓'],
            ['name' => 'Not in control', 'icon' => '😵'],
            ['name' => 'Happy', 'icon' => '😊'],
            ['name' => 'Sad', 'icon' => '😢'],
            ['name' => 'Sensitive', 'icon' => '🥺'],
            ['name' => 'Angry', 'icon' => '😠'],
            ['name' => 'Confident', 'icon' => '💪'],
            ['name' => 'Excited', 'icon' => '🎉'],
            ['name' => 'Irritable', 'icon' => '😤'],
            ['name' => 'Anxious', 'icon' => '😰'],
            ['name' => 'Insecure', 'icon' => '🫣'],
            ['name' => 'Grateful', 'icon' => '🙏'],
            ['name' => 'Indifferent', 'icon' => '😐'],
            ['name' => 'Powerful', 'icon' => '⚡'],
            ['name' => 'Unstoppable', 'icon' => '🚀'],
        ];

        DB::table('moods')->insert($moods);
    }
}
