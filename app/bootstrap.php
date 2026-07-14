<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\MySqlConnection;
use App\Models\Favorites;
use App\Models\UserRepository;
use App\Services\Config;
use App\Services\GoogleAuthService;
use App\Services\GoogleClientFactory;
use App\Services\SessionService;

// 1. Environment Configuration
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// 2. Application Configuration Registry
$config = new Config('config/app.php');

// 3. Global Session Infrastructure Initialization
$sessionConfig = $config->get('session');
$sessionService = new SessionService(
    $sessionConfig['lifetime'],
    $sessionConfig['cookie_name']
);
$sessionService->start();

// 4. Database Engine Initialization
$database = new MySqlConnection($config->get('db'));
$pdo = $database->getConnection();

// 5. Data Access Repositories Initialization
$userRepository = new UserRepository($pdo);

// 6. External Authentication Gateways Configuration (Google OAuth2)
$googleClientFactory = new GoogleClientFactory(
    $config->get('google_auth_client_id'),
    $config->get('google_auth_client_secret'),
    $config->get('google_auth_redirect_uri'),
);
$authService = new GoogleAuthService($googleClientFactory->create(), $userRepository);

// 7. Favorite stories
$favorites = new Favorites($pdo, $authService);

/**
 * Return fully provisioned application dependency array map to the execution entrypoint.
 */
return [
    'config' => $config,
    'pdo' => $pdo,
    'authService' => $authService,
    'userRepository' => $userRepository,
    'favorites' => $favorites,
];