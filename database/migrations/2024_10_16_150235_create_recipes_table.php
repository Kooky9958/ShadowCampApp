<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecipesTable extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('uri')->unique(); // URI should be unique
            $table->string('label');
            $table->json('images')->nullable(); // Replaced thumbnail with images as JSON
            $table->string('source');
            $table->string('url')->nullable(); // Use snake_case for consistency
            $table->integer('yield')->nullable(); // Yield can be nullable
            $table->json('health_labels')->nullable(); // Use snake_case for consistency
            $table->integer('calories')->nullable();
            $table->json('cuisine_type')->nullable(); // Use JSON for arrays
            $table->json('meal_type')->nullable(); // Use JSON for arrays
            $table->json('dish_type')->nullable(); // Use JSON for arrays
            $table->json('ingredient_lines')->nullable(); // Use JSON for arrays
            $table->timestamps(); // Adds created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
}
