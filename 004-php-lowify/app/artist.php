<?php

require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

// Connexion à la base
try {
    $db = new DatabaseManager(
        dsn: 'mysql:host=mysql;dbname=lowify;charset=utf8mb4',
        username: 'lowify',
        password: 'lowifypassword'
    );
} catch (PDOException $ex) {
    echo "Erreur lors de la connexion à la base de données : " . $ex->getMessage();
    exit;
}

$artistId = $_GET["id"] ?? 0;

// --- Fonction formatage durée mm:ss ---
function formatDuration(int $seconds): string {
    $minutes = floor($seconds / 60);
    $remainingSeconds = $seconds % 60;
    return sprintf("%d:%02d", $minutes, $remainingSeconds);
}

// Récupération des données
try {

    // Infos artiste
    $artistChoice = $db->executeQuery(
        "SELECT * FROM artist WHERE id = :artistId",
        ['artistId' => $artistId]
    );

    if (empty($artistChoice)) {
        header("Location: error.php?message=" . urlencode("Artiste introuvable."));
        exit;
    }

    $artistInfo = $artistChoice[0];

    // Top 5 chansons
    $songChoice = $db->executeQuery(<<<SQL
        SELECT 
            s.id AS song_id,
            s.name AS song_name,
            s.duration AS song_duration,
            s.note AS song_note,
            a.cover AS album_cover,
            a.name AS album_name
        FROM song s
        INNER JOIN album a ON s.album_id = a.id
        WHERE s.artist_id = :artistId
        ORDER BY s.note DESC
        LIMIT 5
    SQL, ['artistId' => $artistId]);

    // Albums
    $albumChoice = $db->executeQuery(<<<SQL
        SELECT 
            id,
            name,
            cover
        FROM album
        WHERE artist_id = :artistId
    SQL, ['artistId' => $artistId]);

} catch (PDOException $ex) {
    echo "Erreur SQL : " . $ex->getMessage();
    exit;
}


// HTML albums
$albumHtml = "";

foreach ($albumChoice as $album) {
    $albumId = $album["id"];
    $albumCover = $album['cover'];
    $albumName  = $album['name'] ?? "Titre inconnu";

    $albumHtml .= <<<HTML
        <div class="album-card">
            <img src="$albumCover" alt="Cover album">
            <div><a href="album.php?id=$albumId">$albumName</a></div>
        </div>
    HTML;
}

// HTML chansons
$songHtml = "";

foreach ($songChoice as $song) {
    $songName = $song['song_name'] ?? "Titre inconnu";
    $formattedDuration = formatDuration((int)$song['song_duration']);
    $songNote = $song['song_note'];
    $albumCover = $song['album_cover'];
    $albumName = $song['album_name'];

    $songHtml .= <<<HTML
        <div class="song-card">
            <img src="$albumCover" alt="Album cover" class="song-cover">
            <div class="song-info">
                <strong>$songName</strong><br>
                <span>Durée : $formattedDuration</span><br>
                <span>Note : $songNote</span><br>
                <small>Album : $albumName</small>
            </div>
        </div>
    HTML;
}


// Page complète
$html = <<<HTML
    <head>
        <style>
            body {
                margin: 0;
                padding: 20px;
                font-family: Arial, sans-serif;
                background-color: #121212;
                color: white;
            }

            .app {
                max-width: 1000px;
                margin: auto;
            }

            h1 {
                margin-bottom: 10px;
                font-weight: 600;
            }

            h2 {
                margin-top: 25px;
                margin-bottom: 10px;
                font-weight: 600;
            }

            .album-list {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                gap: 15px;
            }

            .album-card {
                background-color: #181818;
                padding: 10px;
                border-radius: 8px;
                text-align: center;
                transition: transform 0.15s;
            }

            .album-card:hover {
                transform: scale(1.05);
            }

            .album-card img {
                width: 100%;
                height: 140px;
                object-fit: cover;
                border-radius: 6px;
            }

            .song-list {
                margin-top: 15px;
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .song-card {
                display: flex;
                background-color: #181818;
                padding: 10px;
                border-radius: 8px;
                align-items: center;
                gap: 10px;
            }

            .song-cover {
                width: 55px;
                height: 55px;
                border-radius: 6px;
                object-fit: cover;
            }

            .note-badge {
                margin-left: auto;
                background-color: #1DB954;
                padding: 4px 8px;
                border-radius: 12px;
                font-size: 12px;
                color: black;
                font-weight: bold;
            }
        </style>
    </head>
    <button>
    <a href="index.php">Retourner à l'accueil</a>
    </button>
    <h1>{$artistInfo['name']}</h1>

    <h2>Albums</h2>
    <div class="album-list">
        $albumHtml
    </div>

    <h2>Top 5 chansons</h2>
    <div class="song-list">
        $songHtml
    </div>
HTML;

echo (new HTMLPage(title: "Artist - Lowify"))
    ->setupNavigationTransition()
    ->addContent($html)
    ->render();
