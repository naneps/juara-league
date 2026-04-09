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
        Schema::create('stages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tournament_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('type'); // single_elim, double_elim, round_robin, swiss
            $table->integer('order')->default(1);
            $table->json('settings')->nullable(); // For BO settings, match configs, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stages');
    }
};
