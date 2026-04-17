<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tournament;
use App\Models\Stage;

// Check MPL tournament
$t = Tournament::where('slug', 'mpl')->first();
echo "Tournament: {$t->title} | slug={$t->slug} | id={$t->id}\n";
echo "Owner: {$t->user_id}\n";

// Check stages via relationship
$stages = $t->stages;
echo "\nStages via relationship: " . $stages->count() . "\n";
foreach ($stages as $s) {
    echo "  ID={$s->id} | {$s->name} | status={$s->status} | tournament_id={$s->tournament_id}\n";
}

// Check stages via direct query  
$stagesDirect = Stage::where('tournament_id', $t->id)->get();
echo "\nStages via direct query: " . $stagesDirect->count() . "\n";
foreach ($stagesDirect as $s) {
    echo "  ID={$s->id} | {$s->name} | status={$s->status}\n";
    echo "  Matches: " . $s->matches()->count() . "\n";
}

// Check user
$user = \App\Models\User::where('email', 'nandang@mail.com')->first();
if ($user) {
    echo "\nUser nandang: id={$user->id}\n";
    echo "Is owner? " . ($user->id === $t->user_id ? 'YES' : 'NO') . "\n";
}

// Test the API response for stages
echo "\n=== API Response Preview ===\n";
$stagesWithRelations = Stage::where('tournament_id', $t->id)->with(['groups', 'matches'])->get();
echo json_encode($stagesWithRelations->toArray(), JSON_PRETTY_PRINT);
