<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\GoogleAuthService;
use PDO;

/**
 * Model managing user favorite stories, handling state both in-memory and in the database.
 */
class Favorites
{
    /**
     * @var int[] Cached list of favorite story IDs for the currently authenticated user.
     */
    private array $favorites = [];

    /**
     * @param PDO $pdo Active database connection instance.
     * @param GoogleAuthService $authService Service handling user authentication.
     */
    public function __construct(
        private readonly PDO               $pdo,
        private readonly GoogleAuthService $authService
    ) {
        $this->loadFavorites();
    }

    /**
     * Toggles a story in the user's favorites list (Adds if missing, removes if present).
     *
     * @param int $storyId The unique ID of the story to toggle.
     * @return bool True if the database operation succeeded, false otherwise.
     */
    public function toggle(int $storyId): bool
    {
        if (!$this->authService->isLoggedIn()) {
            return false;
        }

        $userId = $this->authService->getUserId();

        if ($this->is($storyId)) {
            // Remove the story from the database repository
            $stmt = $this->pdo->prepare('
                DELETE FROM user_favorite_stories 
                WHERE user_id = ? AND story_id = ?
            ');
            $success = $stmt->execute([$userId, $storyId]);

            // Synchronize internal in-memory cache on success
            if ($success) {
                $this->favorites = array_diff($this->favorites, [$storyId]);
            }
        } else {
            // Persist the new favorite story relationship
            $stmt = $this->pdo->prepare('
                INSERT INTO user_favorite_stories (user_id, story_id) 
                VALUES (?, ?)
            ');
            $success = $stmt->execute([$userId, $storyId]);

            // Synchronize internal in-memory cache on success
            if ($success) {
                $this->favorites[] = $storyId;
            }
        }

        return $success;
    }

    /**
     * Retrieves the entire array of favorite story IDs for the current user.
     *
     * @return int[] Array of favorite story IDs.
     */
    public function getList(): array
    {
        return $this->favorites;
    }

    /**
     * Checks if a specific story ID is marked as a favorite.
     *
     * @param int $id The story ID to verify.
     * @return bool True if the story is in the favorites list, false otherwise.
     */
    public function is(int $id): bool
    {
        return in_array($id, $this->favorites, true);
    }

    /**
     * Generates a database selection filter based on the incoming request parameters.
     *
     * @param array<string, mixed> $requestData Query or request data parameters.
     * @return array<string, array<int>> Constructed selection filter array.
     */
    public function getStoriesFilter(array $requestData): array
    {
        $filter = [];

        // If the favorites flag is present, filter stories strictly by the user's cached list
        $favoriteIds = isset($requestData['favorites']) ? $this->getList() : [];
        if (!empty($favoriteIds)) {
            $filter['ids'] = $favoriteIds;
        }

        return $filter;
    }

    /**
     * Populates the internal in-memory cache with the user's favorites from the database.
     */
    private function loadFavorites(): void
    {
        if (!$this->authService->isLoggedIn()) {
            return;
        }

        $userId = $this->authService->getUserId();

        $stmt = $this->pdo->prepare('
            SELECT story_id 
            FROM user_favorite_stories 
            WHERE user_id = ?
        ');
        $stmt->execute([$userId]);

        // Fetch all values from the single story_id column directly into a flat array
        $this->favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}