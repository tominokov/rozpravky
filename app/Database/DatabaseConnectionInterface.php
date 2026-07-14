<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

/**
 * Interface for managing and retrieving database connection instances.
 */
interface DatabaseConnectionInterface
{
    /**
     * Retrieves the active PDO database connection instance.
     *
     * @return PDO The configured PDO connection object.
     */
    public function getConnection(): PDO;
}
