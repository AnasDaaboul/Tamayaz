<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseStudentElosTable extends Migration
{
    public function up()
    {
        Schema::create('course_student_elos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses');
            $table->foreignId('student_id')->constrained('students');
            $table->float('elo_rating')->default(1200);
            $table->timestamps();

            $table->unique(['course_id', 'student_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('course_student_elos');
    }
}