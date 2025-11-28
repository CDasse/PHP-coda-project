<?php
// files included
require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';
require_once 'inc/utils.inc.php';

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

$idAlbum = $_GET["id"];
$error = "error.php?message=Album inconnu";

$albumInfos = [];

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

    // redirection to error page if idArtist doesn't exist
    if (sizeof($albumInfos) == 0) {
        header("Location: $error");
        exit;
    }

} catch (PDOException $ex) {
    $error = "error.php?message=Album inconnu";
    header("Location: $error");
    exit;
}

$albumInfosInArray = $albumInfos[0];

$albumName = $albumInfosInArray['album_name'];
$albumCover = $albumInfosInArray['album_cover'];
$albumReleaseDate = $albumInfosInArray['album_release_date'];
$artistId = $albumInfosInArray['artist_id'];
$artistName = $albumInfosInArray['artist_name'];

$albumReleaseDateInDMY = dateInDMY($albumReleaseDate);

$albumInfoAsHTML = <<<HTML
    <div>
        <img src="$albumCover" alt="Photo de l'album">
        <div>
            <p><a href="artist.php?id=$artistId" $artistName</p>
            <p>$albumReleaseDateInDMY</p>
        </div>
    </div>
HTML;

$songsOfAlbum = [];

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

$artistAlbumsAsHTML = "";

foreach ($songsOfAlbum as $song) {
    $songName = $song['song_name'];
    $songDuration = $song['song_duration'];
    $songNote = $song['song_note'];

    $songDurationInMMSS = timeInMMSS($songDuration);

    $artistAlbumsAsHTML .= <<<HTML
        <div>
            <p>$songName</p>
            <p>$songDurationInMMSS</p>
            <p>$songNote</p>
        </div>
HTML;
}

$html = <<< HTML
<h1>$albumName</h1>
<div>
$albumInfoAsHTML
$artistAlbumsAsHTML
</div>
HTML;

echo (new HTMLPage(title: "Lowify - $albumName"))
    ->addContent($html)
    ->render();