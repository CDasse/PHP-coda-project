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

$idArtist = $_GET["id"];
$error = "error.php?message=Artiste inconnu";

$artistInfos = [];

// extract artists data from data base
try {
    $artistInfos = $db->executeQuery(<<<SQL
    SELECT *
    FROM artist
    WHERE id = :idArtist
    SQL, ["idArtist" => $idArtist]);

    // redirection to error page if idArtist doesn't exist
    if ($artistInfos == null) {
        header("Location: $error");
        exit;
    }

} catch (PDOException $ex) {
    header("Location: $error");
    exit;
}

// check that our array is only line
if (1 !== sizeof($artistInfos)) {
    echo "Erreur lors de la requête en base de donnée";
}

$artistInfosInArray = $artistInfos[0];

$artistName = $artistInfosInArray['name'];
$artistCover = $artistInfosInArray['cover'];
$artistBio = $artistInfosInArray['biography'];
$artistMonthlyListeners = $artistInfosInArray['monthly_listeners'];

function numberWithLetter(int $number): string{
    if ($number >= 1000000) {
        return round(($number / 1000000),1) . 'M';
    } else if ($number >= 1000) {
        return round(($number / 1000),1) . 'k';
    } else {
        return (string)$number;
}
}

$artistMonthlyListenersInLetter = numberWithLetter($artistMonthlyListeners);

$artistInfoAsHTML = <<<HTML
    <div>
        <img src="$artistCover" alt="Photo de l'artiste">
        <div>
            <p>$artistBio</p>
            <p>$artistMonthlyListenersInLetter</p>
        </div>
    </div>
HTML;

$artistTop5Songs = [];
// extract top 5 songs from artist
try {
    $artistTop5Songs = $db->executeQuery(<<<SQL
    SELECT
        song.name AS song_name,
        song.duration AS song_duration,
        song.note AS song_note,
        album.cover AS album_cover
    FROM song
    INNER JOIN album ON album.artist_id = song.artist_id
    WHERE song.artist_id = :idArtist
    ORDER BY song.note DESC
    LIMIT 5
SQL, ["idArtist" => $idArtist]);
} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

$artistTop5SongsAsHTML = "";

function timeInMMSS(int $number): string{
    $minutes = floor($number / 60);
    $secondes = $number % 60;
    return $minutes . ':' . $secondes;
}

foreach ($artistTop5Songs as $song) {
    $songName = $song['song_name'];
    $songDuration = $song['song_duration'];
    $songNote = $song['song_note'];
    $songCover = $song['album_cover'];

    $songDurationInMMSS = timeInMMSS($songDuration);

    $artistTop5SongsAsHTML .= <<<HTML
        <div>
            <img src="$songCover" alt="Photo de l'artiste">
            <div>
                <h5>$songName</h5>
                <p>$songDurationInMMSS</p>
                <p>$songNote</p>
            </div>
        </div>
HTML;
}

$artistAlbums = [];
// extract albums of the artist
try {
    $artistAlbums = $db->executeQuery(<<<SQL
    SELECT
        album.id AS album_id,
        album.name AS album_name,
        album.cover AS album_cover,
        album.release_date AS album_release_date
    FROM album
    WHERE album.artist_id = :idArtist
    ORDER BY album.release_date DESC
SQL, ["idArtist" => $idArtist]);
} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

$artistAlbumsAsHTML = "";

function dateInDMY (string $date) : string {
    $dateObj = new DateTime($date);
    $dateInDMY = $dateObj->format('d-m-Y');
    return $dateInDMY;
}

foreach ($artistAlbums as $album) {
    $albumId = $album['album_id'];
    $albumName = $album['album_name'];
    $albumCover = $album['album_cover'];
    $albumReleaseDate = $album['album_release_date'];

    $albumReleaseDateInDMY = dateInDMY($albumReleaseDate);

    $artistAlbumsAsHTML .= <<<HTML
        <div>
        <a href="album.php?id=$albumId">
            <img src="$albumCover" alt="Photo de l'artiste">
            <div>
                <h5>$albumName</h5>
                <p>$albumReleaseDateInDMY</p>
            </div>
        </a>
        </div>
HTML;
}

$html = <<< HTML
<h1>$artistName</h1>
<div>
    $artistInfoAsHTML
    $artistTop5SongsAsHTML
    $artistAlbumsAsHTML
</div>
HTML;

echo (new HTMLPage(title: "Lowify - $artistName"))
    ->addContent($html)
    ->render();