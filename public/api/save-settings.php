<?php

declare(strict_types=1);

use App\Controllers\Api\SettingsController;

$services = require_once __DIR__ . '/../../app/bootstrap.php';
extract($services);

$settingsController = new SettingsController($authService, $userRepository);
$settingsController->save();
