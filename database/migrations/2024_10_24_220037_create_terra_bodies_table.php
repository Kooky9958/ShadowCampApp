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
        Schema::create('terra_bodies', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(TerraUserProvider::class)->constrained();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->json('raw_data')->nullable();
            $table->json('metadata')->nullable();
            $table->json('blood_pressure_data')->nullable();
            $table->json('device_data')->nullable();
            $table->json('glucose_data')->nullable();
            $table->json('heart_data')->nullable();
            $table->json('hydration_data')->nullable();
            $table->json('ketone_data')->nullable();
            $table->json('measurements_data')->nullable();
            $table->json('oxygen_data')->nullable();
            $table->json('temperature_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terra_bodies');
    }
};
