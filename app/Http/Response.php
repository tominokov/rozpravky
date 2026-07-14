<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Utility class for handling standardized HTTP API responses.
 */
class Response
{
    /**
     * Sends a JSON response with the specified status code and terminates execution.
     *
     * @param array<mixed> $data Data to be encoded into JSON format.
     * @param int $statusCode HTTP response status code (default: 200 OK).
     */
    public static function json(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    /**
     * Sends a standardized error response in JSON format and terminates execution.
     *
     * @param string $message Descriptive error message.
     * @param int $statusCode HTTP response status code (default: 400 Bad Request).
     */
    public static function error(string $message, int $statusCode = 400): void
    {
        self::json(['error' => $message], $statusCode);
    }
}