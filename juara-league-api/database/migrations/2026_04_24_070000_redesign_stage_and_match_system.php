<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create match_participants table for FFA & Duel support
        Schema::create('match_participants', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('match_id')->constrained('tournament_matches')->onDelete('cascade');
            $table->foreignUlid('participant_id')->nullable()->constrained('participants')->nullOnDelete();
            $table->unsignedInteger('slot')->nullable(); // 1 = P1, 2 = P2, etc.
            
            // Stats & Results
            $table->decimal('score', 10, 2)->default(0); // For goals, kills, or total score
            $table->unsignedInteger('rank')->nullable(); // For FFA ranking (1st, 2nd, etc.)
            $table->boolean('is_winner')->default(false);
            $table->json('metadata')->nullable(); // For extra stats like kills, deaths, etc.
            
            $table->timestamps();
        });

        // 2. Migrate existing data from tournament_matches to match_participants
        $matches = DB::table('tournament_matches')->get();
        foreach ($matches as $match) {
            // Participant 1
            if ($match->participant_1_id) {
                DB::table('match_participants')->insert([
                    'id' => strtolower((string) \Illuminate\Support\Str::ulid()),
                    'match_id' => $match->id,
                    'participant_id' => $match->participant_1_id,
                    'is_winner' => ($match->winner_id === $match->participant_1_id),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Participant 2
            if ($match->participant_2_id) {
                DB::table('match_participants')->insert([
                    'id' => strtolower((string) \Illuminate\Support\Str::ulid()),
                    'match_id' => $match->id,
                    'participant_id' => $match->participant_2_id,
                    'is_winner' => ($match->winner_id === $match->participant_2_id),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Remove legacy columns from stages table
        Schema::table('stages', function (Blueprint $table) {
            $table->dropColumn(['bo_format']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->string('bo_format')->default('bo1')->after('status');
        });

        Schema::dropIfExists('match_participants');
    }
};
