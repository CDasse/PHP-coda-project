<?php

// files included
require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';
require_once 'inc/utils.inc.php';

$host = "mysql";
$dbname = "lowify";
$username = "lowify";
$password = "lowifypassword";

$db = null;
$idAlbum = $_GET["id"];
$error = "error.php?message=Album inconnu";

$albumInfos = [];
$albumInfoAsHTML = "";
$songsOfAlbum = [];
$songsOfAlbumAsHTML = "";

/**
 * Initialize the data base
 *
 * @param string $host name of the host
 * @param string $dbname name of the data base
 * @param string $username name of the user
 * @param string $password password of the data base
 **/
try {
    $db = new DatabaseManager(
        dsn: "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        username: $username,
        password: $password
    );
} catch (PDOException $ex) {
    echo "Erreur lors de la connexion à la base de données: " . $ex->getMessage();
    exit;
}

/**
 * Query album information with the corresponding artist
 *
 * This query retrieves the album name, its cover, release date,
 * and the artist associated.
 **/
try {
    $albumInfos = $db->executeQuery(<<<SQL
    SELECT 
        album.name AS album_name,
        album.artist_id AS artist_id,
        album.cover AS album_cover,
        album.release_date AS album_release_date,
        artist.name AS artist_name
    FROM album
    INNER JOIN artist ON album.artist_id = artist.id
    WHERE album.id = :idAlbum
    SQL, ["idAlbum" => $idAlbum]);

    // redirection to error page if album doesn't exist
    if (sizeof($albumInfos) == 0) {
        header("Location: $error");
        exit;
    }

} catch (PDOException $ex) {
    $error = "error.php?message=Album inconnu";
    header("Location: $error");
    exit;
}

// converting the result into a simple array
$albumInfosInArray = $albumInfos[0];

// storing album information in variables
$albumName = $albumInfosInArray['album_name'];
$albumCover = $albumInfosInArray['album_cover'];
$albumReleaseDate = $albumInfosInArray['album_release_date'];
$artistId = $albumInfosInArray['artist_id'];
$artistName = $albumInfosInArray['artist_name'];

// formatting release date as DD/MM/YYYY
$albumReleaseDateInDMY = dateInDMY($albumReleaseDate);

// generating the HTML block containing album information
$albumInfoAsHTML = <<<HTML
    <div>
        <img src="$albumCover" alt="Photo de l'album">
        <div>
            <p><a href="artist.php?id=$artistId">$artistName</a></p>
            <p>$albumReleaseDateInDMY</p>
        </div>
    </div>
HTML;

/**
 * Query all the songs of the current album
 *
 * This query returns each song name, duration, and note,
 * ordered by song id in ascending order.
 **/
try {
    $songsOfAlbum = $db->executeQuery(<<<SQL
    SELECT 
        song.name AS song_name,
        song.duration AS song_duration,
        song.note AS song_note
    FROM song
    WHERE song.album_id = :idAlbum
    ORDER BY song.id ASC
    SQL, ["idAlbum" => $idAlbum]);

} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

// generating HTML for each song of the album
foreach ($songsOfAlbum as $song) {
    $songName = $song['song_name'];
    $songDuration = $song['song_duration'];
    $songNote = $song['song_note'];

    // convert duration into MM:SS format
    $songDurationInMMSS = timeInMMSS($songDuration);

    $songsOfAlbumAsHTML .= <<<HTML
        <div>
            <p>$songName</p>
            <p>$songDurationInMMSS</p>
            <p>$songNote</p>
        </div>
HTML;
}

// final HTML structure of the page
$html = <<< HTML
<h1>$albumName</h1>
<div>
$albumInfoAsHTML
$songsOfAlbumAsHTML
</div>
HTML;

// displaying the page using HTMLPage class
echo (new HTMLPage(title: "Lowify - $albumName"))
    ->addContent($html)
    ->render();