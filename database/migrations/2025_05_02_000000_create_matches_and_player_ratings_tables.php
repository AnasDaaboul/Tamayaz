<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matchings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player1_id')->constrained('students');
            $table->foreignId('player2_id')->nullable()->constrained('students');
            $table->foreignId('course_id')->constrained('courses');
            $table->foreignId('winner_id')->nullable()->constrained('students');
            $table->enum('status', ['waiting', 'in_progress', 'completed', 'cancelled']);
            $table->integer('player1_score')->default(0);
            $table->integer('player2_score')->default(0);
            $table->integer('player1_elo_change')->nullable();
            $table->integer('player2_elo_change')->nullable();
            $table->integer('current_question_index')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        Schema::create('player_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('course_id')->constrained('courses');
            $table->integer('rating')->default(1200); // Starting Elo rating
            $table->integer('matches_played')->default(0);
            $table->integer('wins')->default(0);
            $table->integer('losses')->default(0);
            $table->integer('draws')->default(0);
            $table->timestamps();

            $table->unique(['student_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
        Schema::dropIfExists('player_ratings');
    }
};