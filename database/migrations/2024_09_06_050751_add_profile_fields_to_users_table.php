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
            $table->string('gender')->nullable()->after('email');
            $table->integer('age')->nullable()->after('gender');
            $table->decimal('height', 5, 2)->nullable()->after('age');
            $table->decimal('weight', 5, 2)->nullable()->after('height');
            $table->string('address_line1')->nullable()->after('weight');
            $table->string('address_line2')->nullable()->after('address_line1');
            $table->json('hobbies')->nullable()->after('address_line2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['gender', 'age', 'height', 'weight', 'address_line1', 'address_line2', 'hobbies']);
        });
    }
};
