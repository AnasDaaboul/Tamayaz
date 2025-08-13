<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_lifelines', function (Blueprint $table) {
            $table->boolean('fourth_lifeline_used')->default(false)->after('third_lifeline_used');
            $table->boolean('updown_lifeline_used')->default(false)->after('fourth_lifeline_used');
        });
    }

    public function down(): void
    {
        Schema::table('match_lifelines', function (Blueprint $table) {
            $table->dropColumn(['fourth_lifeline_used', 'updown_lifeline_used']);
        });
    }
};