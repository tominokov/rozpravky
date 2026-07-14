<?php

declare(strict_types=1);

use App\Http\Response;
use App\Models\Player;

$services = require_once __DIR__ . '/../../app/bootstrap.php';
extract($services);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Method not allowed', 405);
}

$player = new Player($pdo);
$storiesFilter = $favorites->getStoriesFilter($_GET);
$stories = $player->getStories($storiesFilter);

Response::json($stories);
