<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::create('senjem', function (Blueprint $table) {
        $table->id();
        $table->foreignId('competitive_match_id')->constrained();
        $table->unsignedBigInteger('question_id');
        $table->foreignId('user_id')->constrained();
        $table->unsignedInteger('points_earned');
        $table->string('question_difficulty');
        $table->boolean('is_correct');
        $table->unsignedTinyInteger('player_number')->comment('1 or 2');
        $table->boolean('answered')->default(false);
        $table->unique(['competitive_match_id', 'question_id', 'player_number']);
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('senjem');
}
};