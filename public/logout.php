<?php

declare(strict_types=1);

$services = require_once __DIR__ . '/../app/bootstrap.php';
extract($services);

$authService->logout();

header('Location: ' . $config->getUrl());
exit;
