<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_content_resource_lists', function (Blueprint $table) {
            $table->id();
            $table->string('url_id')->unique();
            $table->string('name')->unique();
            $table->string('description');
            $table->json('audience');
            $table->dateTime('date_created');
            $table->dateTime('date_release')->nullable();
            $table->dateTime('date_expiry')->nullable();
            $table->boolean('published');
            $table->integer('release_relative_day')->nullable();
            $table->boolean('release_relative_persistent')->nullable();
            $table->string('coverimage_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_content_resource_lists');
    }
};
