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
        Schema::create('terra_nutrition', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(TerraUserProvider::class)->nullable()->constrained();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->json('raw_data')->nullable();
            $table->json('summary')->nullable();
            $table->json('meals')->nullable();
            $table->json('drink_samples')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terra_nutrition');
    }
};
