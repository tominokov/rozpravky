<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Http\Response;
use App\Models\Favorites;
use App\Services\GoogleAuthService;

/**
 * Controller handling user favorite stories management via API.
 */
class FavoriteController extends ApiController
{
    /**
     * @param GoogleAuthService $authService Service handling user authentication.
     * @param Favorites $favorites Model managing user favorite records.
     */
    public function __construct(
        GoogleAuthService          $authService,
        private readonly Favorites $favorites
    ) {
        parent::__construct($authService);
    }

    /**
     * Toggles the favorite status of a specific story for the authenticated user.
     */
    public function toggle(): void
    {
        // Validates method, login state, and parses incoming JSON payload
        $data = $this->parseJsonPostRequest();

        // Validate required request payload parameters
        if (!isset($data['story_id'])) {
            Response::error('Missing story_id', 400);
        }

        $storyId = (int)$data['story_id'];

        // Execute the toggle operation in the database
        $success = $this->favorites->toggle($storyId);

        // Return operation success state and the current actual favorite status
        Response::json([
            'success' => $success,
            'is_favorite' => $this->favorites->is($storyId)
        ]);
    }
}
