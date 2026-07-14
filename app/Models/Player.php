<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Model handling track, playlist, and story playback data retrieval.
 */
readonly class Player
{
    /**
     * @param PDO $pdo Active database connection instance.
     */
    public function __construct(private PDO $pdo) {}

    /**
     * Retrieves a list of stories, optionally filtered by specific IDs.
     *
     * @param array{ids?: int[]} $filter Optional filter parameters containing allowed story IDs.
     * @return array<int, array<string, mixed>> List of stories with their ID and name.
     */
    public function getStories(array $filter = []): array
    {
        $query = "SELECT id, name FROM `stories`";
        $params = [];

        // Apply a dynamic IN clause fallback condition if IDs filter is provided
        if (!empty($filter['ids'])) {
            $placeholders = implode(',', array_fill(0, count($filter['ids']), '?'));
            $query .= ' WHERE id IN (' . $placeholders . ')';
            $params = array_values($filter['ids']);
        }

        $query .= ' ORDER BY name';

        $statement = $this->pdo->prepare($query);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves details for a single specific story by its unique ID.
     *
     * @param int $storyId The unique ID of the requested story.
     * @return array<string, mixed>|null The story record array, or null if not found/invalid.
     */
    public function getSelectedStory(int $storyId): ?array
    {
        if ($storyId <= 0) {
            return null;
        }

        $statement = $this->pdo->prepare("SELECT id, name FROM `stories` WHERE id = ?");
        $statement->execute([$storyId]);

        $result = $statement->fetch();

        return $result ?: null;
    }
}
