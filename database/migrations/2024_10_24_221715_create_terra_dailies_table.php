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
        Schema::create('terra_dailies', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(TerraUserProvider::class)->constrained();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->json('raw_data')->nullable();
            $table->json('metadata')->nullable();
            $table->json('calories_data')->nullable();
            $table->json('device_data')->nullable();
            $table->json('met_data')->nullable();
            $table->json('oxygen_data')->nullable();
            $table->json('heart_rate_data')->nullable();
            $table->json('distance_data')->nullable();
            $table->json('stress_data')->nullable();
            $table->json('tag_data')->nullable();
            $table->json('scores')->nullable();
            $table->json('strain_data')->nullable();
            $table->json('active_durations_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terra_dailies');
    }
};
