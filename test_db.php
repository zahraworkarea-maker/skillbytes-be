<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$assessment = App\Models\Assessment::where('slug', 'assesmen-oop')->first();
if(!$assessment) die('not found');

$res = [];
foreach($assessment->questions as $q) {
  $opts = [];
  foreach($q->options as $o) {
    $opts[$o->label] = $o->is_correct;
  }
  $res[] = $opts;
}

echo json_encode($res, JSON_PRETTY_PRINT);
