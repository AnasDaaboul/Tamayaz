<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_lifelines', function (Blueprint $table) {
            $table->unsignedTinyInteger('player_number')->after('student_id');
            // Drop the old unique constraint
            // $table->dropUnique(['competitive_match_id', 'student_id']);
            // Add new unique constraint including player_number
            // $table->unique(['competitive_match_id', 'student_id', 'player_number']);
        });
    }

    public function down(): void
    {
        Schema::table('match_lifelines', function (Blueprint $table) {
            // Restore the old unique constraint
            $table->dropUnique(['competitive_match_id', 'student_id', 'player_number']);
            $table->unique(['competitive_match_id', 'student_id']);
            $table->dropColumn('player_number');
        });
    }
};