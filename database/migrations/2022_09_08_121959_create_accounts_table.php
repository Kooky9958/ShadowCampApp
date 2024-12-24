<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('ig_user')->nullable()->unique();
            $table->string('fb_user')->nullable()->unique();
            $table->json('products')->nullable();
            $table->json('questionnaire')->nullable();
            $table->dateTime('begining');
            $table->string('industry_occupation')->nullable();
            $table->boolean('declared_healthy');
            $table->boolean('accepted_tc');
            $table->json('audience')->nullable();
            $table->json('communication')->nullable();
            $table->foreignIdFor(User::class)->nullable()->constrained();
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
        Schema::dropIfExists('accounts');
    }
};
