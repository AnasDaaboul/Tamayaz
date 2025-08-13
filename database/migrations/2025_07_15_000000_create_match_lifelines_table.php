<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_lifelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competitive_match_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->boolean('change_question_used')->default(false);
            $table->boolean('get_options_used')->default(false);
            $table->boolean('third_lifeline_used')->default(false);
            $table->timestamps();

            // Ensure each student has only one lifeline record per match
            // $table->unique(['competitive_match_id', 'student_id' , 'player']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_lifelines');
    }
};