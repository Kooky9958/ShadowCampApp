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
        Schema::table('video_playlists', function (Blueprint $table) {
            $table->text('tags')->nullable()->after('description');// Add the tags column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('video_playlists', function (Blueprint $table) {
            $table->dropColumn('tags');
        });
    }
};
