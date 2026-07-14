<?php

declare(strict_types=1);

namespace App\Services;

use Google\Client;

/**
 * Factory class responsible for initializing and configuring the Google API Client instance.
 */
readonly class GoogleClientFactory
{
    /**
     * @param string $clientId The unique OAuth client ID issued by Google Cloud Console.
     * @param string $clientSecret The private client secret key matched to the client ID.
     * @param string $redirectUri The absolute callback target URL allowed to process authorization codes.
     */
    public function __construct(
        private string $clientId,
        private string $clientSecret,
        private string $redirectUri
    ) {}

    /**
     * Instantiates and prepares a fully configured Google API Client ready for authentication flows.
     *
     * @return Client The configured Google API Client runtime instance.
     */
    public function create(): Client
    {
        $client = new Client();
        $client->setClientId($this->clientId);
        $client->setClientSecret($this->clientSecret);
        $client->setRedirectUri($this->redirectUri);

        // Standard profile identity
        $client->addScope('email');

        return $client;
    }
}