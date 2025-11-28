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
        $error = "error.php?message=Artiste inconnu";
        header('Location: '.$error);
        exit;
    }

} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    $error = "error.php?message=Artiste inconnu";
    header('Location: '.$error);
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
        return round(($number / 1000000),2) . 'M';
    } else if ($number >= 1000) {
        return round(($number / 1000),2) . 'K';
    } else {
        return (string)$number;
}
}

$artistMonthlyListenersInLetter = numberWithLetter($artistMonthlyListeners);

$artistInfoAsHTML = <<<HTML
    <div>
        <img src="$artistCover" alt="Photo de l'artiste">
        <div>
            <h5>$artistName</h5>
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
    $secondes = round(($number % 60), 2);
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

//function dateInDMY (string $date) : string {
//    return date_format($date, 'd-m-Y');
//}

foreach ($artistAlbums as $album) {
    $albumName = $album['album_name'];
    $albumCover = $album['album_cover'];
    $albumReleaseDate = $album['album_release_date'];

//    $albumReleaseDateInDMY = dateInDMY($albumReleaseDate);

    $artistAlbumsAsHTML .= <<<HTML
        <div>
            <img src="$albumCover" alt="Photo de l'artiste">
            <div>
                <h5>$albumName</h5>
                <p>$albumReleaseDate</p>
            </div>
        </div>
HTML;
}

$html = <<< HTML
<h1></h1>
<div>
    $artistInfoAsHTML
    $artistTop5SongsAsHTML
    $artistAlbumsAsHTML
</div>
HTML;

echo (new HTMLPage(title: "Lowify - $artistName"))
    ->addContent($html)
    ->render();