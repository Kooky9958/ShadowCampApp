<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Foreign key referencing users table
            $table->string('content_id'); // The ID of the content (video or recipe)
            $table->string('content_type'); // To specify if the content is a video or a recipe
            $table->unsignedBigInteger('parent_comment_id')->nullable(); // Optional foreign key for nested comments

            $table->text('comment_text')->nullable(); // The content of the comment
            $table->json('gif')->nullable();
            $table->timestamps(); // Created at and updated at timestamps

            // Foreign key for nested comments
            $table->foreign('parent_comment_id')->references('id')->on('comments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
}
