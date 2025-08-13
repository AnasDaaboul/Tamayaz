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
        Schema::create('course_media', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('media_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_teacher_id')->constrained()->cascadeOnDelete();
            $table->string('file_name')->default('n');
            $table->integer('order')->default(1);

            });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_media');
    }
};
