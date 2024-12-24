<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIngredientsTable extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipe_id'); // Foreign key to recipes
            $table->string('text');
            $table->float('quantity')->nullable(); // Quantity as float to handle decimals
            $table->string('measure')->nullable();
            $table->string('food')->nullable();
            $table->float('weight')->nullable(); // Weight as float for more precision
            $table->string('foodId')->nullable(); // Snake_case for consistency
            $table->timestamps();

            // Define foreign key constraint with cascade on delete
            $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
}
