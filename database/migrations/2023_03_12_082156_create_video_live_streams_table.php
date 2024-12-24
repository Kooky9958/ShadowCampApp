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
        Schema::create('video_live_streams', function (Blueprint $table) {
            $table->id();
            $table->string('url_id')->unique();
            $table->string('name')->unique();
            $table->longText('description');
            $table->string('cdn_vendor')->nullable();
            $table->string('cdn_id')->unique()->nullable();
            $table->string('cdn_data')->nullable();
            $table->json('audience')->nullable();
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
        Schema::dropIfExists('video_live_streams');
    }
};
