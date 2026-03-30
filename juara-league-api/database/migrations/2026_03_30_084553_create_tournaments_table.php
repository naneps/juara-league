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
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('status')->default('draft'); // draft, open, ongoing, finished
            $table->string('mode')->default('online');   // online, offline
            $table->string('bracket_type');             // single, double, round_robin, swiss
            $table->string('venue')->nullable();
            $table->string('banner_url')->nullable();
            $table->bigInteger('prize_pool')->default(0);
            $table->bigInteger('entry_fee')->default(0);
            $table->integer('max_participants')->default(0);
            
            $table->dateTime('registration_start_at')->nullable();
            $table->dateTime('registration_end_at')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
