<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;
use RuntimeException;

/**
 * MySQL database connection provider implementing the connection interface.
 */
class MySqlConnection implements DatabaseConnectionInterface
{
    /**
     * @var PDO|null Cached PDO instance for singleton connection behavior.
     */
    private ?PDO $connection = null;

    /**
     * @param array<string, string|int> $config Database configuration options (host, port, database, charset, username, password).
     */
    public function __construct(private readonly array $config) {}

    /**
     * Establishes and returns the active PDO MySQL connection.
     *
     * @throws RuntimeException If the database connection cannot be established.
     * @return PDO The active PDO connection instance.
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            // Build the Data Source Name string
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );

            try {
                // Initialize PDO connection with recommended production attributes
                $this->connection = new PDO($dsn, $this->config['username'], $this->config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                // Encapsulate PDO exception into a generic runtime exception
                throw new RuntimeException("Database connection failed: " . $e->getMessage(), 0, $e);
            }
        }

        return $this->connection;
    }
}
