<?php

require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

// Connexion à la base de données
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

// Top artistes (Top trending)

try {
    $topArtists = $db->executeQuery(<<<SQL
        SELECT id, name, cover, monthly_listeners
        FROM artist
        ORDER BY monthly_listeners DESC
        LIMIT 5
    SQL);
} catch (PDOException $ex) {
    header("Location: error.php?message=" . urlencode("Erreur SQL (artistes)."));
    exit;
}


//Top sorties (Les 5 albums les plus récents)


try {
    $topRecentAlbums = $db->executeQuery(<<<SQL
        SELECT id, name, cover, release_date
        FROM album
        ORDER BY release_date DESC
        LIMIT 5
    SQL);
} catch (PDOException $ex) {
    header("Location: error.php?message=" . urlencode("Erreur SQL (albums récents)."));
    exit;
}

function formatListeners(int $number): string {
    if ($number >= 1_000_000_000) {
        return round($number / 1_000_000_000, 1) . 'B';
    }
    if ($number >= 1_000_000) {
        return round($number / 1_000_000, 1) . 'M';
    }
    if ($number >= 1_000) {
        return round($number / 1_000, 1) . 'k';
    }
    return (string)$number;
}

// Top albums


try {
    $topRatedAlbums = $db->executeQuery(<<<SQL
        SELECT 
            a.id,
            a.name,
            a.cover,
            (
                SELECT AVG(s.note)
                FROM song s
                WHERE s.album_id = a.id
            ) AS avg_note
        FROM album a
        ORDER BY avg_note DESC
        LIMIT 5
    SQL);
} catch (PDOException $ex) {
    header("Location: error.php?message=" . urlencode("Erreur SQL (albums notés)."));
    exit;
}


// Génération du HTML


//Top artistes
$htmlTopArtists = "<h2>Top trending</h2><div class='artist-list'>";

foreach ($topArtists as $artist) {
    $name = htmlspecialchars($artist["name"]);
    $cover = htmlspecialchars($artist["cover"]);
    $id = (int)$artist["id"];
    $listeners = formatListeners((int)$artist["monthly_listeners"]);

    $htmlTopArtists .= <<<HTML
        <a href="artist.php?id=$id" class="artist-card">
            <img src="$cover" alt="$name">
            <div class="title">$name</div>
            <div class="subtitle">$listeners auditeurs / mois</div>
        </a>
    HTML;
}




//Top sorties
$htmlTopRecent = "<h2>Top sorties</h2><div class='album-list'>";

foreach ($topRecentAlbums as $album) {
    $name = htmlspecialchars($album["name"]);
    $cover = htmlspecialchars($album["cover"]);
    $id = (int)$album["id"];
    $date = htmlspecialchars($album["release_date"]);

    $htmlTopRecent .= <<<HTML
        <div class="album-card">
            <img src="$cover" alt="$name">
            <div class="title">$name</div>
            <div class="subtitle">Sorti le $date</div>
        </div>
    HTML;
}



//Top albums
$htmlTopRated = "<h2>Top albums</h2><div class='album-list'>";

foreach ($topRatedAlbums as $album) {
    $name = htmlspecialchars($album["name"]);
    $cover = htmlspecialchars($album["cover"]);
    $id = (int)$album["id"];
    $note = round((float)$album["avg_note"], 2);

    $htmlTopRated .= <<<HTML
        <div class="album-card">
            <img src="$cover" alt="$name">
            <div class="title">$name</div>
            <div class="subtitle">Note moyenne : $note</div>
        </div>
    HTML;
}


//Page finale du html
$finalHTML = <<<HTML
<head>
        <style>
        /* -------------------------------------
      GLOBAL
-------------------------------------- */
body {
    margin: 0;
    padding: 0;
    background-color: #121212;
    color: white;
    font-family: Arial, Helvetica, sans-serif;
}

h2 {
    margin-left: 25px;
    margin-top: 30px;
}

/* -------------------------------------
      SECTIONS
-------------------------------------- */
.home-section {
    margin-bottom: 40px;
}

/* Scroll horizontal pour les listes */
.artist-list,
.album-list {
    display: flex;
    gap: 20px;
    padding: 20px 25px;
    overflow-x: auto;
    scroll-behavior: smooth;
}

/* Style scrollbar */
.artist-list::-webkit-scrollbar,
.album-list::-webkit-scrollbar {
    height: 8px;
}

.artist-list::-webkit-scrollbar-thumb,
.album-list::-webkit-scrollbar-thumb {
    background: #2c2c2c;
    border-radius: 10px;
}

/* -------------------------------------
      ARTIST CARDS
-------------------------------------- */
.artist-card {
    display: block;
    width: 160px;
    padding: 15px;
    background-color: #181818;
    border-radius: 12px;
    text-align: center;
    text-decoration: none;
    color: white;
    transition: 0.25s;
    flex-shrink: 0;
}

.artist-card:hover {
    background-color: #282828;
    transform: scale(1.07);
}

.artist-card img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 50%; /* Style Spotify pour les artistes */
    margin-bottom: 10px;
}

.title {
    font-size: 1rem;
    font-weight: bold;
    margin-top: 5px;
}

.subtitle {
    font-size: 0.9rem;
    color: #b3b3b3;
}

/* -------------------------------------
      ALBUM CARDS
-------------------------------------- */
.album-card {
    width: 160px;
    padding: 15px;
    background-color: #181818;
    border-radius: 12px;
    text-align: center;
    transition: 0.25s;
    flex-shrink: 0;
}

.album-card:hover {
    background-color: #282828;
    transform: scale(1.07);
}

.album-card img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 10px; /* Albums : carrés arrondis */
    margin-bottom: 10px;
}

/* -------------------------------------
      LINK SIMPLE
-------------------------------------- */
a {
    color: #1db954;
    text-decoration: none;
    margin-left: 25px;
}

a:hover {
    text-decoration: underline;
}
.page-artists{
font-size: 50px;
box-sizing: border-box;

color: #1db954;
}
</style>
</head>
    <div class="page-artists">
    <a href="artists.php"> Accéder à la page des Artistes</a>
    </div>
    <div class="home-section">
        $htmlTopArtists
    </div>

    <div class="home-section">
        $htmlTopRecent
    </div>

    <div class="home-section">
        $htmlTopRated
    </div>
HTML;

//Rendu finale

echo (new HTMLPage(title: "Accueil - Lowify"))
    ->setupNavigationTransition()
    ->addContent($finalHTML)
    ->render();
