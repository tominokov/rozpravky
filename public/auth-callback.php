<?php

declare(strict_types=1);

$services = require_once __DIR__ . '/../app/bootstrap.php';
extract($services);

$code = $_GET['code'] ?? null;
if ($code !== null) {
    try {
        $authService->authenticateWithCode($code);
    } catch (Exception $e) {
        // Here you can log the error (e.g. logger->error($e->getMessage()))
        throw $e;
    }
}

header('Location: /');
exit;
