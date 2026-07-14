<?php

declare(strict_types=1);

use App\Controllers\Api\SettingsController;
use App\Http\Response;

$services = require_once __DIR__ . '/../../app/bootstrap.php';
extract($services);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Method not allowed', 405);
}

$settingsController = new SettingsController($authService, $userRepository);
$settingsController->load();
