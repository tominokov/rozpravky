<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Favorites;
use App\Models\Player;

/**
 * Controller handling the main application index and story selection.
 */
class IndexController
{
    /**
     * @param Player $player Model managing track and story player data.
     * @param Favorites $favorites Model managing user favorite records and filters.
     */
    public function __construct(
        private readonly Player    $player,
        private readonly Favorites $favorites
    ) {}

    /**
     * Renders the main index page with filtered stories and the selected story details.
     *
     * @param array<string, mixed> $requestData Incoming request query or post parameters.
     * @return array<string, mixed> Template data including stories list and current selection.
     */
    public function index(array $requestData): array
    {
        // Resolve database filter based on favorite status and request parameters
        $storiesFilter = $this->favorites->getStoriesFilter($requestData);
        $stories = $this->player->getStories($storiesFilter);

        // Parse and validate the requested story ID from parameters
        $selectedStory = intval($requestData['story'] ?? 0);

        return [
            'stories' => $stories,
            'selectedStory' => $this->player->getSelectedStory($selectedStory),
        ];
    }
}