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
        Schema::create('profile_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->json('goals')->nullable();
            $table->json('mental_health_issues')->nullable();
            $table->string('hair_loss')->nullable();
            $table->string('birth_control')->nullable();
            $table->string('reproductive_disorder')->nullable();
            $table->string('weight_change')->nullable();
            $table->string('coffee_consumption')->nullable();
            $table->string('alcohol_consumption')->nullable();
            $table->string('other_goal')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_questions');
    }
};
