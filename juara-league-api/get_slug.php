<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$t = \App\Models\Tournament::where('title', 'Showcase BO1 - Single Game')->first();
if ($t) {
    echo $t->slug;
} else {
    echo "NOT_FOUND";
}
