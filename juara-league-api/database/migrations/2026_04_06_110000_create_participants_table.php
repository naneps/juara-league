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
        Schema::create('participants', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tournament_id')->constrained()->onDelete('cascade');
            $table->foreignUlid('user_id')->nullable()->constrained()->onDelete('set null'); // For Individual
            $table->foreignUlid('team_id')->nullable()->constrained()->onDelete('set null'); // For Team
            $table->enum('status', ['pending', 'approved', 'rejected', 'disqualified'])->default('pending');
            $table->enum('payment_status', ['free', 'pending', 'paid', 'rejected'])->default('free');
            $table->integer('seed')->nullable();
            $table->text('payment_proof_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
