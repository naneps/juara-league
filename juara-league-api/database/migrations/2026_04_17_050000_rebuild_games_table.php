<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('games');

        Schema::create('games', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('match_id')->constrained('tournament_matches')->onDelete('cascade');
            $table->unsignedInteger('game_number');
            $table->foreignUlid('winner_id')->nullable()->constrained('participants')->nullOnDelete();
            $table->unsignedInteger('score_p1')->nullable();
            $table->unsignedInteger('score_p2')->nullable();
            $table->string('status')->default('created'); // created, corrected
            $table->timestamps();

            $table->unique(['match_id', 'game_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');

        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
