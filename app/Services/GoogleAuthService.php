<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\UserRepository;
use Exception;
use Google\Client as GoogleClient;
use Google\Service\Oauth2 as GoogleOauth2;

/**
 * Service handling Google OAuth2 authentication flow, user session, and account synchronization.
 */
readonly class GoogleAuthService
{
    /**
     * @param GoogleClient $googleClient Wrapper for Google OAuth API client endpoints.
     * @param UserRepository $userRepository Repository managing user database records.
     */
    public function __construct(
        private GoogleClient   $googleClient,
        private UserRepository $userRepository
    ) {}

    /**
     * Authenticates the user using Google OAuth code and manages session/database.
     *
     * @param string $code Authorization code received from the Google frontend redirect callback.
     * @throws Exception If an unhandled error occurs during token or user profile extraction.
     * @return bool True if authentication and registration/login succeeded, false on token failure.
     */
    public function authenticateWithCode(string $code): bool
    {
        // Exchange authorization code for temporary access tokens
        $token = $this->googleClient->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            return false;
        }

        $this->googleClient->setAccessToken($token['access_token']);

        // Initialize OAuth profile endpoint and fetch authenticated identity profile
        $googleService = new GoogleOauth2($this->googleClient);
        $googleUser = $googleService->userinfo->get();

        // Match external identity key against target persistence layer
        $user = $this->userRepository->findByGoogleId($googleUser->id);

        if (!$user) {
            // Provision a new user account if the record does not exist
            $userId = $this->userRepository->create($googleUser->id, $googleUser->email);
        } else {
            // Synchronize historical access parameters for verified matches
            $userId = (int)$user['id'];
            $this->userRepository->updateLastLogin($userId);
        }

        // Initialize secure operational runtime values into active server scope
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_email'] = $googleUser->email;

        return true;
    }

    /**
     * Generates the target external redirect URI required to begin Google OAuth sequence.
     *
     * @return string Fully structured target gateway URL.
     */
    public function getLoginUrl(): string
    {
        return $this->googleClient->createAuthUrl();
    }

    /**
     * Checks if the client context contains an active verified user session state.
     *
     * @return bool True if a authenticated user ID constraint is active.
     */
    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Retrieves the localized primary identifier associated with the current connection.
     *
     * @return int|null Internal reference index value, or null if unauthenticated.
     */
    public function getUserId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    /**
     * Retrieves the registered corporate/personal contact email from memory.
     *
     * @return string|null Managed identity mailbox reference string, or null if unauthenticated.
     */
    public function getUserEmail(): ?string
    {
        return $_SESSION['user_email'] ?? null;
    }

    /**
     * Invalidates active session data structures, flushes system cookie variables, and terminates session.
     */
    public function logout(): void
    {
        // Explicitly clear superglobal dataset structures
        $_SESSION = [];

        // Remove active state cookie constraints from the endpoint client
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Deallocate backend file resource contexts completely
        session_destroy();
    }
}
