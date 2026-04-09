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
        Schema::create('tournament_approvals', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tournament_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['auto_approved', 'pending_review', 'approved', 'rejected']);
            $table->json('auto_check_log')->nullable();
            $table->foreignUlid('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_approvals');
    }
};
