<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitive_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->text('question_text');
            $table->json('options'); // Store 4 options as JSON array
            $table->string('correct_answer');
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('competitive_match_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matchings')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('competitive_questions')->onDelete('cascade');
            $table->integer('question_order');
            $table->boolean('player1_answered')->default(false);
            $table->boolean('player2_answered')->default(false);
            $table->boolean('player1_correct')->nullable();
            $table->boolean('player2_correct')->nullable();
            $table->float('player1_response_time')->nullable(); // in seconds
            $table->float('player2_response_time')->nullable(); // in seconds
            $table->boolean('is_tiebreaker')->default(false);
            $table->timestamps();

            $table->unique(['match_id', 'question_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitive_match_questions');
        Schema::dropIfExists('competitive_questions');
    }
};