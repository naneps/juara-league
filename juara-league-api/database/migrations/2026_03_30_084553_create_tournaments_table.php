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
        Schema::create('tournaments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUlid('sport_id')->constrained()->onDelete('cascade');
            $table->string('title', 150);
            $table->string('slug', 170)->unique();
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable();
            $table->enum('status', ['draft', 'registration', 'ongoing', 'completed'])->default('draft');
            $table->enum('approval_status', ['auto_approved', 'pending_review', 'approved', 'rejected'])->default('auto_approved');
            $table->enum('mode', ['open', 'invite'])->default('open');
            $table->string('bracket_type', 50)->nullable();
            $table->enum('participant_type', ['individual', 'team']);
            $table->integer('team_size')->nullable();
            $table->integer('max_participants')->default(0);
            $table->decimal('entry_fee', 12, 2)->default(0);
            $table->decimal('prize_pool', 12, 2)->nullable();
            $table->text('prize_description')->nullable();
            $table->string('venue', 200)->nullable();
            $table->string('banner_url')->nullable();
            
            $table->timestamp('registration_start_at')->nullable();
            $table->timestamp('registration_end_at')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
