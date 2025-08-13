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
        // Schema::table('chapters', function (Blueprint $table) {
        //     $table->dropForeign(['course_id']);
        //     $table->dropColumn('course_id');
        //     $table->foreignId('course_teacher_id')->nullable()->constrained('course_teachers')->cascadeOnDelete()->after('id');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropForeign(['course_teacher_id']);
            $table->dropColumn('course_teacher_id');
            $table->foreignId('course_id')->constrained()->cascadeOnDelete()->after('id');
        });
    }
};
