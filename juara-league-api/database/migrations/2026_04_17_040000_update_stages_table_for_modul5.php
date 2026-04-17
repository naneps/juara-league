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
        Schema::table('stages', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('type'); // pending, ongoing, completed
            $table->string('bo_format')->default('bo1')->after('status'); // bo1, bo3, bo5, bo7
            $table->unsignedInteger('participants_advance')->nullable()->after('bo_format');
            $table->unsignedInteger('groups_count')->nullable()->after('participants_advance');
            $table->unsignedInteger('participants_per_group')->nullable()->after('groups_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'bo_format',
                'participants_advance',
                'groups_count',
                'participants_per_group',
            ]);
        });
    }
};
