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
        Schema::table('videos', function (Blueprint $table) {
            $table->integer('release_relative_day')->nullable();
            $table->boolean('release_relative_persistent')->nullable();
        });

        Schema::table('video_playlists', function (Blueprint $table) {
            $table->integer('release_relative_day')->nullable();
            $table->boolean('release_relative_persistent')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
