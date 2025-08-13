<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_competitive_question_locks', function (Blueprint $table) {
            $table->id();

            // Use plain unsignedBigInteger without any shortcut
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('competitive_question_id');

            $table->boolean('locked')->default(false);
            $table->timestamps();

            // $table->unique(['student_id', 'competitive_question_id']);
            $table->unique(['student_id', 'competitive_question_id'], 'scq_lock_unique');


            // Use manually named constraints (short)
            $table->foreign('student_id', 'scq_lock_student_fk')
                ->references('id')->on('students')->onDelete('cascade');

            $table->foreign('competitive_question_id', 'scq_lock_question_fk')
                ->references('id')->on('competitive_questions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('student_competitive_question_locks', function (Blueprint $table) {
            // Drop foreign keys manually (must match the names)
            $table->dropForeign('scq_lock_student_fk');
            $table->dropForeign('scq_lock_question_fk');
        });

        Schema::dropIfExists('student_competitive_question_locks');
    }
};
