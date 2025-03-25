<?php
$myapi_url = ""; // Your API URL
$proxy_url = ""; // Your Proxy URL
$id = $_GET['id'] ?? '';
$ep = $_GET['ep'] ?? '';
$server = $_GET['server'] ?? 'hd-2'; // Default server: hd-2
$type = $_GET['type'] ?? 'sub'; // Default type: sub

// Function to fetch video URL
function fetchVideoUrl($myapi_url, $id, $ep, $server, $type)
{
    $api_url = "$myapi_url/api/v2/hianime/episode/sources?animeEpisodeId=$id&ep=$ep&server=$server&category=$type";
    $json = file_get_contents($api_url);

    if (!$json) {
        return null;
    }

    $episode = json_decode($json, true);

    if (!isset($episode['data']['sources'][0]['url'])) {
        return null;
    }

    return $episode;
}

// Function to fetch available servers
function fetchAvailableServers($myapi_url, $id, $ep)
{
    $servers_url = "$myapi_url/api/v2/hianime/episode/servers?animeEpisodeId=$id&ep=$ep";
    $servers_json = file_get_contents($servers_url);

    if (!$servers_json) {
        return null;
    }

    return json_decode($servers_json, true);
}

// If type is 'raw', check if 'sub' is available
if ($type === 'raw') {
    $servers = fetchAvailableServers($myapi_url, $id, $ep);

    if (!$servers) {
        die("Error: Could not fetch servers data");
    }

    // Check if 'sub' is available
    if (!empty($servers['data']['sub'])) {
        $type = 'sub';
    }
}

// Fetch video URL for the selected type
$episode = fetchVideoUrl($myapi_url, $id, $ep, $server, $type);

if (!$episode) {
    die("Failed to Get the Video. Please come Back Later Or Refresh The Page");
}

if (!isset($episode['data']['sources'][0]['url'])) {
    die("Failed to Get the Video. Please come Back Later Or Refresh The Page");
}

// Add proxy to the video URL using the configurable proxy URL
$video = "$proxy_url?url=" . urlencode($episode['data']['sources'][0]['url']);

// Extract data from API response
$tracks = [];
$thumbnails = '';
$introStart = $episode['data']['intro']['start'] ?? null;
$introEnd = $episode['data']['intro']['end'] ?? null;
$outroStart = $episode['data']['outro']['start'] ?? null;
$outroEnd = $episode['data']['outro']['end'] ?? null;

foreach ($episode['data']['tracks'] ?? [] as $track) {
    if ($track['kind'] === 'captions') {
        $tracks[] = $track;
    } elseif ($track['kind'] === 'thumbnails') {
        $thumbnails = $track['file'] ?? '';
    }
}

// Generate chapters VTT
$vttContent = "WEBVTT\n\n";
if ($introStart !== null && $introEnd !== null) {
    $vttContent .= gmdate('H:i:s', $introStart) . ".000 --> " . gmdate('H:i:s', $introEnd) . ".000\n";
    $vttContent .= "Intro\n\n";
}
if ($outroStart !== null && $outroEnd !== null) {
    $vttContent .= gmdate('H:i:s', $outroStart) . ".000 --> " . gmdate('H:i:s', $outroEnd) . ".000\n";
    $vttContent .= "Outro\n\n";
}
$chaptersVtt = 'data:text/vtt;base64,' . base64_encode($vttContent);

// Prepare skip times for JavaScript
$skipTimes = [];
if ($introStart !== null && $introEnd !== null) {
    $skipTimes[] = ['start' => $introStart, 'end' => $introEnd, 'type' => 'intro'];
}
if ($outroStart !== null && $outroEnd !== null) {
    $skipTimes[] = ['start' => $outroStart, 'end' => $outroEnd, 'type' => 'outro'];
}
$skipTimesJSON = json_encode($skipTimes);
$title = "Anoboy VidStack Player - Episode $ep";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vidstack@^1.9.8/player/styles/default/theme.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vidstack@^1.9.8/player/styles/default/layouts/video.min.css" />
    <script type="module" src="https://cdn.jsdelivr.net/npm/vidstack@^1.9.8/cdn/with-layouts/vidstack.js"></script>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            height: 100%;
        }
        .vds-audio-layout, .vds-video-layout {
            --media-brand: #007BFF;
            --media-controls-color: #ffffff;
            --media-font-family: "Poppins", sans-serif;
        }
        media-player {
            width: 100%;
            height: 100vh;
            aspect-ratio: 16/9;
            position: relative;
        }
        .skip-button {
            padding: 10px 20px;
            border-radius: 5.5em;
            border: 2px solid #fff;
            background-color: rgb(255, 255, 255);
            color: black;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            font-family: system-ui, -apple-system, sans-serif;
            display: none;
            transition: all 0.3s ease;
            animation: fadeIn 0.3s ease-in-out;
            pointer-events: auto;
            position: absolute;
            bottom: 85px;
            right: 25px;
            z-index: 1000;
        }
        .skip-button:hover {
            background-color: rgba(210, 209, 211, 0.8);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        /* Competitor's subtitle styling */
        .vds-captions {
            --cue-bg-color: rgba(0, 0, 0, 0);
            --cue-default-font-size: var(--media-cue-font-size, calc(var(--overlay-height) / 100 * 6));
        }
        .vds-captions [data-part=cue] {
            backdrop-filter: blur(0px);
            text-shadow: rgb(0, 0, 0) -2px 0px 1px, rgb(0, 0, 0) 2px 0px 1px, rgb(0, 0, 0) 0px -2px 1px, rgb(0, 0, 0) 0px 2px 1px, rgb(0, 0, 0) -1px 1px 1px, rgb(0, 0, 0) 1px 1px 1px, rgb(0, 0, 0) 1px -1px 1px, rgb(0, 0, 0) 1px 1px 1px;
            font-weight: 700;
            font-family: TrebuchetMS, Helvetica, sans-serif;
        }
    </style>
</head>
<body>
    <media-player
        id="media-player"
        title="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>"
        src="<?= htmlspecialchars($video, ENT_QUOTES, 'UTF-8') ?>"
        crossorigin
        playsinline
        autoplay
        keyboard
        autopause
    >
        <media-provider>
            <?php foreach ($tracks as $track): ?>
            <track
                src="<?= htmlspecialchars($track['file'], ENT_QUOTES, 'UTF-8') ?>"
                label="<?= htmlspecialchars($track['label'], ENT_QUOTES, 'UTF-8') ?>"
                kind="captions"
                srclang="<?= htmlspecialchars(explode(' ', $track['label'])[0]) ?>"
                <?= isset($track['default']) && $track['default'] ? 'default' : '' ?>
            />
            <?php endforeach; ?>
            <track
                src="<?= htmlspecialchars($chaptersVtt, ENT_QUOTES, 'UTF-8') ?>"
                kind="chapters"
                default
            />
        </media-provider>
        <media-video-layout thumbnails="<?= htmlspecialchars($thumbnails, ENT_QUOTES, 'UTF-8') ?>">
            <media-controls>
                <media-time-slider>
                    <media-preview-thumbnail></media-preview-thumbnail>
                    <media-chapters-thumbnail></media-chapters-thumbnail>
                </media-time-slider>
            </media-controls>
        </media-video-layout>
        <button id="skip-button" class="skip-button">Skip</button>
    </media-player>

    <script type="module">
        const player = document.querySelector('#media-player');
        const skipButton = document.getElementById('skip-button');
        const skipTimes = <?= $skipTimesJSON ?>;

        console.log('Skip times loaded:', skipTimes);

        // Resume functionality
        const storageKey = `player-progress-<?= htmlspecialchars($id) ?>-<?= htmlspecialchars($ep) ?>`;
        const savedTime = localStorage.getItem(storageKey);

        if (savedTime) {
            player.addEventListener('loaded-metadata', () => {
                player.currentTime = parseFloat(savedTime);
            }, { once: true });
        }

        player.addEventListener('time-update', () => {
            localStorage.setItem(storageKey, player.currentTime);
        });

        // Skip button functionality
        player.addEventListener('time-update', () => {
            const currentTime = player.currentTime;
            let shouldShowSkip = false;
            let skipToTime = null;
            let skipLabel = 'Skip';

            for (const skip of skipTimes) {
                if (currentTime >= skip.start && currentTime < skip.end) {
                    shouldShowSkip = true;
                    skipToTime = skip.end;
                    skipLabel = `Skip ${skip.type.charAt(0).toUpperCase() + skip.type.slice(1)}`;
                    break;
                }
            }

            skipButton.textContent = skipLabel;
            skipButton.style.display = shouldShowSkip ? 'block' : 'none';
            console.log('Fullscreen:', document.fullscreenElement, 'Button visible:', shouldShowSkip);
        });

        skipButton.addEventListener('click', () => {
            const currentTime = player.currentTime;
            for (const skip of skipTimes) {
                if (currentTime >= skip.start && currentTime < skip.end) {
                    player.currentTime = skip.end;
                    skipButton.style.display = 'none';
                    break;
                }
            }
        });

        // Error handling
        player.addEventListener('error', (event) => {
            console.error("Player Error:", event.detail);
        });

        // Attempt autoplay
        player.addEventListener('loaded-metadata', () => {
            player.play().catch(error => console.log('Autoplay failed:', error));
        }, { once: true });
    </script>
</body>
</html>
