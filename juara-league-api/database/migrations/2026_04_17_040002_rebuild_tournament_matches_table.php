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
        Schema::dropIfExists('tournament_matches');

        Schema::create('tournament_matches', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('stage_id')->constrained('stages')->onDelete('cascade');
            $table->foreignUlid('group_id')->nullable()->constrained('groups')->onDelete('cascade');
            $table->unsignedInteger('round')->default(1);
            $table->unsignedInteger('match_number')->default(1);

            // Participants
            $table->foreignUlid('participant_1_id')->nullable()->constrained('participants')->nullOnDelete();
            $table->foreignUlid('participant_2_id')->nullable()->constrained('participants')->nullOnDelete();
            $table->foreignUlid('winner_id')->nullable()->constrained('participants')->nullOnDelete();

            // Match state
            $table->string('status')->default('upcoming'); // upcoming, ongoing, completed, bye
            $table->string('bracket_side')->nullable(); // upper, lower, grand_final (for double elim)

            // Bracket linking
            $table->foreignUlid('next_match_winner_id')->nullable()->constrained('tournament_matches')->nullOnDelete();
            $table->foreignUlid('next_match_loser_id')->nullable()->constrained('tournament_matches')->nullOnDelete();

            // Scores
            $table->json('scores')->nullable(); // Per-game scores

            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_matches');

        Schema::create('tournament_matches', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
