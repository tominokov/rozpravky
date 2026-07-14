<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Service managing secure server-side session initialization and cookie parameters.
 */
readonly class SessionService
{
    /**
     * @param int $lifetime Session and cookie expiration time in seconds.
     * @param string $sessionName Custom cookie name replacement for PHPSESSID.
     */
    public function __construct(
        private int    $lifetime,
        private string $sessionName = 'APP_SESSION'
    ) {}

    /**
     * Initializes a safe, production-ready session context if none exists.
     */
    public function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Set custom session cookie name before initialization
            session_name($this->sessionName);

            // Synchronize garbage collection timeout thresholds with the target lifetime
            ini_set('session.cookie_lifetime', (string) $this->lifetime);
            ini_set('session.gc_maxlifetime', (string) $this->lifetime);

            // Enforce strict security flags on the session cookie payload
            session_set_cookie_params([
                'lifetime' => $this->lifetime,
                'path' => '/',
                'domain' => '',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            session_start();
        }
    }
}