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
        Schema::create('terra_sleeps', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(TerraUserProvider::class)->constrained();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->json('raw_data')->nullable();
            $table->json('metadata')->nullable();
            $table->json('device_data')->nullable();
            $table->json('heart_rate_data')->nullable();
            $table->json('readiness_data')->nullable();
            $table->json('respiration_data')->nullable();
            $table->json('sleep_durations_data')->nullable();
            $table->json('temperature_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terra_sleeps');
    }
};
