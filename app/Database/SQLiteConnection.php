<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;
use RuntimeException;

/**
 * SQLite database connection provider implementing the connection interface.
 */
class SQLiteConnection implements DatabaseConnectionInterface
{
    /**
     * @var PDO|null Cached PDO instance for singleton connection behavior.
     */
    private ?PDO $connection = null;

    /**
     * @param string $databasePath Absolute or relative path to the SQLite database file.
     */
    public function __construct(private readonly string $databasePath) {}

    /**
     * Establishes and returns the active PDO SQLite connection.
     *
     * @throws RuntimeException If the database connection cannot be established.
     * @return PDO The active PDO connection instance.
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            try {
                // Initialize PDO connection with standard error handling attributes
                $this->connection = new PDO('sqlite:' . $this->databasePath, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);

                // Enforce foreign key constraints for integrity
                $this->connection->exec('PRAGMA foreign_keys = ON;');
            } catch (PDOException $e) {
                // Encapsulate PDO exception into a generic runtime exception
                throw new RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
            }
        }

        return $this->connection;
    }
}
