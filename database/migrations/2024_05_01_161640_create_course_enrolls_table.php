<?php

use App\Models\Cobon;
use App\Models\Course;
use App\Models\CourseTeacher;
use App\Models\Student;
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
        Schema::create('course_enrolls', function (Blueprint $table) {
            $table->id();
            // $table->foreignIdFor(Cobon::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(Student::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(CourseTeacher::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_enrolls');
    }
};