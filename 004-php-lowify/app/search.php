<?php

// files included
require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

// initialize data base manager
$host = "mysql";
$dbname = "lowify";
$username = "lowify";
$password = "lowifypassword";

$db = null;
try {
    // check if the connexion is ok
    $db = new DatabaseManager(
        dsn: "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        username: $username,
        password: $password
    );
} catch (PDOException $ex) {
    echo "Erreur lors de la connexion à la base de données: " . $ex->getMessage();
    exit;
}

$search = $_POST["search"];
$searchLike = "%". $search . "%";

$artistsFound = [];

try {
    $artistsFound = $db->executeQuery(<<<SQL
    SELECT
            name,
            id,
            cover
    FROM artist
    WHERE (
        MATCH(name) AGAINST(:search IN NATURAL LANGUAGE MODE) OR
        name LIKE :searchLike
    )
SQL, ["search" => $search, "searchLike" => $searchLike]);
} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

$artistsFoundAsHTML = "";

if (sizeof($artistsFound) == 0) {
    $artistsFoundAsHTML .= <<<HTML
        <p>Aucun artiste ne correspond à votre recherche.</p>
    HTML;
} else {
foreach ($artistsFound as $artist) {
    $artistId = $artist['id'];
    $artistName = $artist['name'];
    $artistCover = $artist['cover'];

    $artistsFoundAsHTML .= <<<HTML
        <div>
        <a href="artist.php?id=$artistId">
            <img src="$artistCover" alt="Photo de l'artiste">
            <p>$artistName</p>
        </a>
        </div>
        HTML;
}
}

$albumsFound = [];

try {
    $albumsFound = $db->executeQuery(<<<SQL
    SELECT 
        album.name AS album_name,
        album.id AS album_id,
        album.artist_id AS artist_id,
        album.cover AS album_cover,
        album.release_date AS album_release_date,
        artist.name AS artist_name
    FROM album
    INNER JOIN artist ON album.artist_id = artist.id
    WHERE (
        MATCH(album.name) AGAINST(:search IN NATURAL LANGUAGE MODE) OR
        album.name LIKE :searchLike
    )
SQL, ["search" => $search, "searchLike" => $searchLike]);
} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

$albumsFoundAsHTML = "";

if (sizeof($albumsFound) == 0) {
    $albumsFoundAsHTML .= <<<HTML
        <p>Aucun album ne correspond à votre recherche.</p>
    HTML;
} else {
    foreach ($albumsFound as $album) {
        $albumId = $album['album_id'];
        $albumName = $album['album_name'];
        $albumCover = $album['album_cover'];
        $albumReleaseDate = $album['album_release_date'];
        $artistName = $album['artist_name'];
        $artistId = $album['artist_id'];

        $albumsFoundAsHTML .= <<<HTML
        <div>
        <a href="album.php?id=$albumId">
            <img src="$albumCover" alt="Photo de l'album">
            <p>$albumName - <a href=artist.php?id=$artistId"">$artistName</a></p>
        </a>
        </div>
HTML;
    }
}

$songsFound = [];

try {
    $songsFound = $db->executeQuery(<<<SQL
    SELECT 
        song.name AS song_name,
        song.duration AS song_duration,
        song.note AS song_note,
        album.name AS album_name,
        album.id AS album_id,
        artist.name AS artist_name,
        artist.id AS artist_id
    FROM song
    INNER JOIN album ON song.album_id = album.id
    INNER JOIN artist ON song.artist_id = artist.id
    WHERE (
        MATCH(song.name) AGAINST(:search IN NATURAL LANGUAGE MODE) OR
        song.name LIKE :searchLike
    )
SQL, ["search" => $search, "searchLike" => $searchLike]);
} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

$songsFoundAsHTML = "";

function timeInMMSS(int $number): string{
    $minutes = floor($number / 60);
    $secondes = $number % 60;
    return $minutes . ':' . $secondes;
}

if (sizeof($songsFound) == 0) {
    $songsFoundAsHTML .= <<<HTML
        <p>Aucune chanson ne correspond à votre recherche.</p>
    HTML;
} else {
    foreach ($songsFound as $song) {
        $songName = $song['song_name'];
        $songDuration = $song['song_duration'];
        $songNote = $song['song_note'];
        $albumName = $song['album_name'];
        $albumId = $song['album_id'];
        $artistName = $song['artist_name'];
        $artistId = $song['artist_id'];

        $songDurationInMMSS = timeInMMSS($songDuration);

        $songsFoundAsHTML .= <<<HTML
        <div>
            <p>$songName</p>
            <p>$songDurationInMMSS</p>
            <p>$songNote</p>
            <p><a href="album.php?id=$albumId">$albumName</a></p>
            <p><a href="artist.php?id=$artistId">$artistName</a></p>
        </div>
HTML;
    }
}

$html = <<< HTML
<a href="index.php">Retour à l'accueil</a>
<h1>Recherche : $search</h1>
<h2>Artistes</h2>
$artistsFoundAsHTML
<h2>Albums</h2>
$albumsFoundAsHTML
<h2>Chansons</h2>
$songsFoundAsHTML
HTML;

echo (new HTMLPage(title: "Lowify - Recherche"))
    ->addContent($html)
    ->render();