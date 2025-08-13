<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('competitive_matches', function (Blueprint $table) {
        $table->enum('status', ['in progress', 'finished'])
            ->default('in progress');
    });
}

// ... existing down() method ...

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competitive_matches', function (Blueprint $table) {
            //
        });
    }
};
