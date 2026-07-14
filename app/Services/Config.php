<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

/**
 * Service handling global application configuration, environment settings, and path resolution.
 */
class Config
{
    /**
     * @var array<string, mixed> Multi-dimensional array of loaded configuration key-value pairs.
     */
    private array $settings;

    /**
     * @var string Absolute system path to the root directory of the application.
     */
    private string $appRoot;

    /**
     * @param string $relativeConfigPath Relative path from the application root to the main configuration file.
     * @throws RuntimeException If the specified configuration file does not exist.
     */
    public function __construct(string $relativeConfigPath)
    {
        // Resolve the base root directory by traversing upwards from the current directory structure
        $this->appRoot = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;

        $absolutePath = $this->appRoot . ltrim($relativeConfigPath, '/\\');
        if (!file_exists($absolutePath)) {
            throw new RuntimeException("Configuration file not found: {$absolutePath}");
        }

        // Scope and encapsulate isolated array settings payload directly from filesystem resource
        $this->settings = require $absolutePath;
    }

    /**
     * Retrieves a configuration setting value by its unique key string.
     *
     * @param string $key The configuration identifier key.
     * @param mixed $default Fallback value returned if the target key is not present.
     * @throws RuntimeException If the key is missing from settings and no explicit fallback default is provided.
     * @return mixed Resolved setting variable payload data.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!array_key_exists($key, $this->settings)) {
            // Strict enforcement condition requiring either an initialized key presence or explicit safe fallback parameters
            if ($default === null) {
                throw new RuntimeException("Missing required configuration key: '{$key}'");
            }
            return $default;
        }

        return $this->settings[$key];
    }

    /**
     * Generates an absolute operating system filesystem path prefixed with the verified application root.
     *
     * @param string $relativePath Target relative directory or file structure sequence.
     * @return string Normalized system file locator destination string.
     */
    public function getPath(string $relativePath): string
    {
        return $this->appRoot . ltrim($relativePath, '/\\');
    }

    /**
     * Generates a fully qualified uniform resource locator address targeting internal routing contexts.
     *
     * @param string $relativePath Optional target route path suffix to append to the base domain.
     * @return string Fully compiled absolute URI reference string.
     */
    public function getUrl(string $relativePath = ''): string
    {
        $baseUrl = rtrim($this->get('base_url'), '/');
        $path = ltrim($relativePath, '/');

        return $baseUrl . '/' . $path;
    }
}
