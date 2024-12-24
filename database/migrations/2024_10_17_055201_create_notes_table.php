<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('content_id'); // ID of the recipe or video
            $table->enum('content_type', ['recipe', 'video']); // Type of the content
            $table->text('note_text')->nullable(); // Note content
            $table->date('note_date'); // Date of the note
            $table->timestamps();

            // Indexing for faster searching
            $table->index(['user_id', 'content_id', 'content_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notes');
    }
}
