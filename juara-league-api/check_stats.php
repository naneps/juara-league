<?php

use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\Stage;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$slug = 'bo1-single-game-DkI0'; // Correct slug for the showcase tournament
$tournament = Tournament::where('slug', $slug)->first();

if (!$tournament) {
    echo "Tournament not found: $slug\n";
    exit;
}

echo "Tournament Found: {$tournament->title} ({$tournament->id})\n";

$stages = $tournament->stages;
echo "Stages Count: " . $stages->count() . "\n";

foreach ($stages as $stage) {
    echo "- Stage: {$stage->name} ({$stage->id}), status: {$stage->status}\n";
    $matches = TournamentMatch::where('stage_id', $stage->id)->get();
    echo "  - Matches Count: " . $matches->count() . "\n";
    foreach ($matches as $match) {
        echo "    - M#{$match->match_number}, status: {$match->status}\n";
    }
}

$stageIds = $stages->pluck('id')->toArray();
$total = TournamentMatch::whereIn('stage_id', $stageIds)->count();
$completed = TournamentMatch::whereIn('stage_id', $stageIds)->where('status', 'completed')->count();

echo "\nSummary:\n";
echo "Total Matches: $total\n";
echo "Completed Matches: $completed\n";
if ($total > 0) {
    echo "Rate: " . round(($completed / $total) * 100) . "%\n";
} else {
    echo "Rate: 0% (No matches)\n";
}
