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
        Schema::table('users', function (Blueprint $table) {
            $table->longText('address_line1')->nullable()->after('weight');
            $table->string('city', 255)->nullable()->after('address_line1');
            $table->string('region', 255)->nullable()->after('city');
            $table->string('country', 255)->nullable()->after('region');
            $table->string('postcode', 20)->nullable()->after('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['address_line1', 'city', 'region', 'country', 'postcode']);
        });
    }
};
