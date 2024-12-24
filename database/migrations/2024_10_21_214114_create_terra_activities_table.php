<?php

use App\Models\TerraUserProvider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('terra_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(TerraUserProvider::class)->constrained();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->tinyInteger('activity_type')->nullable()->comment('REFERENCE TO TERRA API ENUM');
            $table->json('raw_data')->nullable();
            $table->json('metadata')->nullable();
            $table->double('work_kilojoules')->nullable();
            $table->integer('stress_score')->nullable();
            $table->json('met_data')->nullable();
            $table->json('laps_data')->nullable();
            $table->json('calories_data')->nullable();
            $table->json('activity_duration')->nullable();
            $table->json('oxygen_data')->nullable();
            $table->json('energy_data')->nullable();
            $table->json('tss_samples_data')->nullable();
            $table->json('device_data')->nullable();
            $table->json('distance_data')->nullable();
            $table->json('polyline_map_data')->nullable();
            $table->json('heart_rate_data')->nullable();
            $table->json('movement_data')->nullable();
            $table->json('strain_data')->nullable();
            $table->json('power_data')->nullable();
            $table->string('cheat_detection', 50)->nullable();
            $table->json('position_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terra_activities');
    }
};
