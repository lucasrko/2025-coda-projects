<?php
require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

//Connexion à la base de données
try {
    $db = new DatabaseManager(
        dsn: 'mysql:host=mysql;dbname=lowify;charset=utf8mb4',
        username: 'lowify',
        password: 'lowifypassword'
    );
} catch (PDOException $ex) {
    header("Location: error.php?message=" . urlencode("Erreur base de données : " . $ex->getMessage()));
    exit;
}

$albumId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($albumId <= 0) {
    header("Location: error.php?message=" . urlencode("ID d'album invalide."));
    exit;
}

try {
    $albumData = $db->executeQuery(
        "SELECT id, name, cover, release_date, artist_id FROM album WHERE id = :id",
        ['id' => $albumId]
    );
} catch (PDOException $ex) {
    header("Location: error.php?message=" . urlencode("Erreur SQL lors de la récupération de l'album."));
    exit;
}

if (empty($albumData)) {
    header("Location: error.php?message=" . urlencode("Album introuvable."));
    exit;
}

$album = $albumData[0];


$artist = null;
$artistName = "Artiste inconnu";
$artistId = 0;
$artistPicture = "/assets/img/placeholder-artist.png"; // placeholder par défaut

if (!empty($album['artist_id'])) {
    try {
        $artistData = $db->executeQuery(
            "SELECT id, name, cover FROM artist WHERE id = :id",
            ['id' => $album['artist_id']]
        );
    } catch (PDOException $ex) {
        $artistData = [];
    }

    if (!empty($artistData)) {
        $artist = $artistData[0];
        $artistId = (int)($artist['id'] ?? 0);
        $artistName = $artist['name'] ?? $artistName;
        $artistPicture = $artist['cover'] ?? $artistPicture;
    }
}

//Récupération des chansons de l'album
try {
    $songs = $db->executeQuery(
        "SELECT id, name, duration, note FROM song WHERE album_id = :id ORDER BY id ASC",
        ['id' => $albumId]
    );
} catch (PDOException $ex) {
    $songs = [];
}

$albumName    = htmlspecialchars($album['name'] ?? 'Album inconnu', ENT_QUOTES, 'UTF-8');
$albumCover   = htmlspecialchars($album['cover'] ?? '/assets/img/placeholder-album.png', ENT_QUOTES, 'UTF-8');
$releaseDate  = htmlspecialchars($album['release_date'] ?? '', ENT_QUOTES, 'UTF-8');
$artistNameEsc = htmlspecialchars($artistName, ENT_QUOTES, 'UTF-8');
$artistPictureEsc = htmlspecialchars($artistPicture, ENT_QUOTES, 'UTF-8');
$artistIdEsc = (int)$artistId;

//Génération du HTML
$html = <<<HTML
<button>
<a href="index.php">Retourner à l'accueil</a>
</button>
<div style="display:flex; gap:24px; padding:20px; align-items:center;">
    <div style="width:220px; flex-shrink:0;">
        <img src="{$albumCover}" alt="Cover de {$albumName}" style="width:100%; border-radius:10px; display:block;">
    </div>
    <div>
        <h1>{$albumName}</h1>
        <h3>Par <a href="artist.php?id={$artistIdEsc}">{$artistNameEsc}</a></h3>
        <p>Sorti le : {$releaseDate}</p>
    </div>
</div>

<h2 style="margin-left:20px;">Liste des titres</h2>
<div style="padding:20px;">
HTML;

if (empty($songs)) {
    $html .= "<p>Aucun titre trouvé pour cet album.</p>";
} else {
    foreach ($songs as $song) {
        $songName = htmlspecialchars($song['name'] ?? 'Titre inconnu', ENT_QUOTES, 'UTF-8');
        $duration = htmlspecialchars($song['duration'] ?? '', ENT_QUOTES, 'UTF-8');
        $note = htmlspecialchars((string)($song['note'] ?? ''), ENT_QUOTES, 'UTF-8');

        $html .= <<<HTML
<head>
    <style>
        /* --- GLOBAL --- */
body {
    margin: 0;
    padding: 20px;
    background-color: #121212;
    font-family: Arial, sans-serif;
    color: white;
}

.album-container {
    max-width: 900px;
    margin: auto;
}

/* --- HEADER ALBUM (Cover + Infos) --- */
.album-header {
    display: flex;
    gap: 25px;
    align-items: center;
    margin-bottom: 30px;
}

.album-cover {
    width: 220px;
    height: 220px;
    object-fit: cover;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
}

.album-info h1 {
    font-size: 42px;
    margin: 0;
}

.album-info .artist-name {
    font-size: 20px;
    color: #b3b3b3;
    margin-top: 5px;
}

/* --- LISTE DES CHANSONS --- */
.track-list {
    margin-top: 20px;
    border-collapse: collapse;
    width: 100%;
}

.track {
    display: grid;
    grid-template-columns: 40px 1fr 80px 60px;
    padding: 12px 10px;
    align-items: center;
    border-radius: 6px;
    transition: background 0.15s;
}

.track:hover {
    background-color: #1f1f1f;
}

.track-number {
    color: #b3b3b3;
    text-align: center;
}

.track-title {
    font-size: 16px;
}

.track-duration,
.track-note {
    text-align: center;
    color: #b3b3b3;
}

/* --- TITRES DES COLONNES --- */
.track-header {
    display: grid;
    grid-template-columns: 40px 1fr 80px 60px;
    padding: 10px;
    margin-bottom: 10px;
    color: #b3b3b3;
    font-size: 14px;
    border-bottom: 1px solid #2a2a2a;
}

/* --- RESPONSIVE --- */
@media (max-width: 700px) {
    .album-header {
        flex-direction: column;
        text-align: center;
    }

    .album-cover {
        width: 180px;
        height: 180px;
    }

    .track {
        grid-template-columns: 30px 1fr 60px 50px;
    }

    .track-header {
        grid-template-columns: 30px 1fr 60px 50px;
    }
}

    </style>
</head>
        <div class="card" style="margin-bottom:10px; display:flex; justify-content:space-between; padding:12px; background:#181818; border-radius:8px;">
            <div style="flex:1;">{$songName}</div>
            <div style="margin-left:12px; color:#b3b3b3;">&nbsp;|&nbsp; ⭐ {$note}</div>
        </div>
HTML;
    }
}

$html .= "</div>";

// Rendu complet page html
echo (new HTMLPage(title: "Album - Lowify"))
    ->setupNavigationTransition()
    ->addContent($html)
    ->render();
