<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Repository handling database operations for the user entity and configuration.
 */
readonly class UserRepository
{
    /**
     * @param PDO $pdo Active database connection instance.
     */
    public function __construct(private PDO $pdo) {}

    /**
     * Retrieves user-specific configuration settings from the database.
     *
     * @param int $userId The unique ID of the user.
     * @return array<string, mixed> Decoded settings payload or an empty array on failure/missing data.
     */
    public function getSettings(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT settings FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['settings'])) {
            return [];
        }

        return json_decode($row['settings'], true) ?: [];
    }

    /**
     * Updates and sanitizes user-specific JSON configuration settings.
     *
     * @param int $userId The unique ID of the user.
     * @param array<string, mixed> $settings Raw incoming settings values to be normalized.
     * @return bool True if the database operation succeeded, false otherwise.
     */
    public function updateSettings(int $userId, array $settings): bool
    {
        // Explicit type-casting ensures data sanitization before persistence
        $settingsJson = json_encode([
            'shuffle' => (int)($settings['shuffle'] ?? 0),
            'volume' => (float)($settings['volume'] ?? 1.0)
        ]);

        $stmt = $this->pdo->prepare('UPDATE users SET settings = :settings WHERE id = :id');

        return $stmt->execute([
            ':settings' => $settingsJson,
            ':id' => $userId
        ]);
    }

    /**
     * Finds a single user record matched by their Google OAuth unique identifier.
     *
     * @param string $googleId The external identifier from Google.
     * @return array<string, mixed>|null The user record dataset, or null if no match found.
     */
    public function findByGoogleId(string $googleId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE google_id = ?');
        $stmt->execute([$googleId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * Creates a new persistent user record and initializes their login tracking.
     *
     * @param string $googleId The external identifier from Google.
     * @param string $email The primary email associated with the authenticated profile.
     * @return int The auto-incremented primary key ID of the newly created user row.
     */
    public function create(string $googleId, string $email): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO users (google_id, email, last_login_at) 
            VALUES (?, ?, NOW())
        ');
        $stmt->execute([$googleId, $email]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Refreshes the last login timestamp for an active user session.
     *
     * @param int $userId The unique ID of the user.
     */
    public function updateLastLogin(int $userId): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
        $stmt->execute([$userId]);
    }
}
