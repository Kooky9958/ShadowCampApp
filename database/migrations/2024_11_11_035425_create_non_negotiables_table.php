<?php

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
        Schema::create('non_negotiables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date'); // Date of the non-negotiable task (e.g., mood for that day)
            $table->string('type'); // Type of non-negotiable (e.g., 'mood', 'water', 'sleep')
            $table->boolean('completed')->default(false); // Whether the task is completed
            $table->timestamps();

            $table->unique(['user_id', 'date', 'type']); // Ensure there's only one record per user per task per day
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('non_negotiables');
    }
};
