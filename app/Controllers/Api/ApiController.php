<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\GoogleAuthService;
use App\Http\Response;

/**
 * Base controller for API endpoints providing request parsing and validation.
 */
abstract class ApiController
{
    /**
     * @param GoogleAuthService $authService Service handling user authentication.
     */
    public function __construct(
        protected GoogleAuthService $authService
    ) {}

    /**
     * Validates the request method, authentication state, and returns the parsed JSON payload.
     *
     * @return array<string, mixed> Parsed JSON data from the request body.
     */
    protected function parseJsonPostRequest(): array
    {
        // Enforce HTTP POST method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Method not allowed', 405);
        }

        // Enforce user authentication
        if (!$this->authService->isLoggedIn()) {
            Response::error('Unauthorized', 401);
        }

        // Read and decode raw JSON input
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        // Validate JSON syntax
        if ($data === null) {
            Response::error('Invalid JSON', 400);
        }

        return $data;
    }
}
