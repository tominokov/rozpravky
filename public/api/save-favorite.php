<?php

declare(strict_types=1);

use App\Controllers\Api\FavoriteController;

$services = require_once __DIR__ . '/../../app/bootstrap.php';
extract($services);

$favoriteController = new FavoriteController($authService, $favorites);
$favoriteController->toggle();
