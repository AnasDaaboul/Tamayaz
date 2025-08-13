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
        Schema::create('competitive_matches', function (Blueprint $table) {
            $table->id();
            $table->string('player1_name');
            $table->string('player2_name');
            $table->integer('player1_score')->default(0);
            $table->integer('player2_score')->default(0);
            $table->json('topics');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitive_matches');
    }
};
