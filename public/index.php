<?php

declare(strict_types=1);

$services = require_once __DIR__ . '/../app/bootstrap.php';
extract($services);

use App\Controllers\IndexController;
use App\Models\Player;

$player = new Player($pdo);
$appController = new IndexController($player, $favorites);

$viewData = $appController->index($_GET);
$stories = $viewData['stories'];
$selectedStory = $viewData['selectedStory'];

$googleTagId = $config->get('google_tag_id', '');
$autoShutdownDuration = $config->get('auto_shutdown_duration', 60 * 60); // 1 hour

?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#262626">
    <title><?= $selectedStory ? $selectedStory['name'] . ' - ' : '' ?>Rozprávky pre deti na dobrú noc</title>
    <link rel="shortcut icon" href="<?= $config->getUrl('static/img/favicon.png') ?>" type="image/png">

    <meta name="description" content="Staré dobré slovenské rozprávky pre deti na dobrú noc">
    <meta name="keywords" content="rozprávky, deti, staré, retro, slovenské, uspávanie, dieťa, dobrú, noc">
    <meta name="robots" content="index, follow">

    <link rel="stylesheet" href="<?= $config->getUrl('static/fontawesome/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= $config->getUrl('static/css/style.css?r=202.60505') ?>">

    <?php if ($googleTagId) { ?>
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $googleTagId ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', '<?= $googleTagId ?>');
        </script>
    <?php } ?>
</head>
<body>
<div class="player">

    <?php if (!$authService->isLoggedIn()) { ?>
        <nav class="user-navbar">
            <a href="<?= htmlspecialchars($authService->getLoginUrl()) ?>" class="google-btn">
                <img src="<?= $config->getUrl('static/img/google.svg') ?>">
                Prihlásiť sa cez Google
            </a>
        </nav>
    <?php } else { ?>
        <nav class="user-navbar">
            <div class="user-profile">
                <i class="fa-solid fa-user user-icon"></i>
                <span class="user-email"><?= $authService->getUserEmail() ?></span>
            </div>
            <div class="user-actions">
                <a href="<?= $config->getUrl('logout.php') ?>"
                   class="logout-btn"
                   onclick="return confirm('Naozaj sa chcete odhlásiť?')">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Odhlásiť sa</span>
                </a>
            </div>
        </nav>
    <?php } ?>

    <div class="wrapper">
        <div class="slider_container sm-hide">
            <i class="fa fa-volume-down"></i>
            <input type="range" min="1" max="100" value="99" class="volume_slider" onchange="updateVolume()">
            <i class="fa fa-volume-up"></i>
        </div>

        <div class="details">
            <div class="track-art"></div>
            <h1 class="track-name"><?= $selectedStory ? $selectedStory['name'] : 'Rozprávky pre deti' ?></h1>
        </div>

        <div class="slider_container">
            <div class="current-time">00:00</div>
            <input type="range" min="0" max="100" value="0" class="seek_slider" onchange="seekTo()">
            <div class="remains-time">00:00</div>
        </div>

        <div class="buttons mb-0">
            <div class="prev-track" onclick="prevTrack()">
                <i class="fa-solid fa-step-backward fa-2x"></i>
            </div>
            <div class="prev-track" onclick="seekBackward()">
                <i class="fa-solid fa-backward fa-2x"></i>
            </div>
            <div class="playPause-track" onclick="playPauseTrack()">
                <i class="fa-solid fa-play-circle fa-5x"></i>
            </div>
            <div class="next-track" onclick="seekForward()">
                <i class="fa-solid fa-forward fa-2x"></i>
            </div>
            <div class="next-track" onclick="nextTrack()">
                <i class="fa-solid fa-step-forward fa-2x"></i>
            </div>
        </div>

        <div class="buttons">
            <div class="random-track" onclick="randomTrack()">
                <i class="fa-solid fa-shuffle fa-2x"></i>
            </div>
            <div class="repeat-track" onclick="repeatTrack()">
                <i class="fa-solid fa-repeat fa-2x" title="repeat"></i>
            </div>
            <div class="repeat-track" onclick="resetAutoStop()">
                <i class="fa-solid fa-hourglass-start fa-1x" title="repeat"></i>
                <div class="auto-shutdown" id="autoShutdown" data-duration="<?= $autoShutdownDuration ?>"></div>
            </div>
        </div>

        <div class="my-3">

            <?php if ($authService->isLoggedIn()) { ?>
                <a href="<?= $config->getUrl('') ?>" class="button mr-1">Všetky</a>
                <a href="<?= $config->getUrl('?favorites') ?>" class="button mr-1">
                    <i class="fa-solid fa-heart red mr-1"></i>
                    Obľúbené
                </a>
            <?php } ?>

            <a href="javascript:void(0);" class="button ml-2" onclick="copyActualTimeToClipboard()">
                <i class="fa fa-link" aria-hidden="true"></i>
            </a>
        </div>

        <div class="mb-1">
            <input type="text" id="searchStory" class="input w-80" placeholder="Vyhľadávanie..." onkeyup="searchInList(this.value)">
        </div>

        <ul class="track-list">
            <?php foreach ($stories as $story) { ?>
                <?php
                $storyId = $story['id'];
                $storyName = $story['name'];
                ?>
                <li data-story-id="<?= $storyId ?>" class="story-row">
                    <a href="<?= $config->getUrl("?story=$storyId") ?>" onclick="return customTrack(<?= $storyId ?>)" class="custom-track">
                        <?= $storyName ?>
                    </a>

                    <?php if ($authService->isLoggedIn()) { ?>
                        <?php $isFavorite = $favorites->is($storyId) ?>

                        <span class="favorite" data-selected="<?= $isFavorite ? 1 : 0 ?>"
                              onclick="switchFavorite(this, <?= $storyId ?>)">
                            <i class="fa-<?= $isFavorite ? 'solid' : 'regular' ?> fa-heart"></i>
                        </span>
                    <?php } ?>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>

<script src="<?= $config->getUrl('static/js/script.js?r=202.60606') ?>"></script>
<script src="<?= $config->getUrl('static/js/favorite.js?r=202.60606') ?>"></script>
<script>
    const BASE_AUDIO_URL = '<?= $config->getUrl('static/rozpravky/') ?>';
    let selectedStory = <?= $selectedStory !== null ? $selectedStory['id'] : 'null' ?>;

    <?php if ($authService->isLoggedIn()) { ?>
        loadUserSettings();
    <?php } ?>
</script>

</body>
</html>
