<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\UserRepository;
use App\Services\GoogleAuthService;
use App\Http\Response;

/**
 * Controller handling user settings management via API.
 */
class SettingsController extends ApiController
{
    /**
     * @param GoogleAuthService $authService Service handling user authentication.
     * @param UserRepository $userRepository Repository managing user database records.
     */
    public function __construct(
        GoogleAuthService               $authService,
        private readonly UserRepository $userRepository
    )
    {
        parent::__construct($authService);
    }

    /**
     * Loads and returns the user configuration or default settings if unauthenticated.
     */
    public function load(): void
    {
        $defaultSettings = [
            'shuffle' => 0,
            'volume' => 1.0,
        ];

        // Return defaults immediately for unauthenticated users
        if (!$this->authService->isLoggedIn()) {
            Response::json($defaultSettings);
        }

        // Fetch user-specific configuration and merge with defaults
        $userSettings = $this->userRepository->getSettings($this->authService->getUserId());
        $response = array_merge($defaultSettings, $userSettings);

        Response::json($response);
    }

    /**
     * Saves the incoming user configuration from a JSON POST request.
     */
    public function save(): void
    {
        // Validates method, login state, and parses incoming JSON payload
        $data = $this->parseJsonPostRequest();

        $success = $this->userRepository->updateSettings(
            $this->authService->getUserId(),
            $data
        );

        Response::json(['success' => $success]);
    }
}
