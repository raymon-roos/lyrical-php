<?php

declare(strict_types=1);

namespace App;

require_once(__DIR__ .  '/../vendor/autoload.php');

for ($i = 0; $i < min(4, count($argv)); $i++) {
    match ($argv[$i]) {
        '--artist', '-a' => $artist = strtolower(trim($argv[$i + 1])) ?? die('missing artist'),
        '--title', '-t' => $title = strtolower(trim($argv[$i + 1])) ?? die('missing title'),
        '--url', '-u' => $title = strtolower(trim($argv[$i + 1])) ?? die('missing url'),
        default => 0 // ¯\_(ツ)_/¯
    };
}

$lyrical = new Lyrical();

echo match (true) {
    !empty($url) => $lyrical->getLyricsFromUrl($url),
    !empty($title) && !empty($artist) => $lyrical->getLyricsFromSearch($title, $artist),
    !empty($title) => $lyrical->getLyricsFromSearch($title),
    default => die('ERROR: failed to parse arguments')
};
