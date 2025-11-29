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

$idArtist = $_GET["id"];
$error = "error.php?message=Artiste inconnu";

$artistInfos = [];
$artistInfoAsHTML = "";
$artistTop5Songs = [];
$artistTop5SongsAsHTML = "";
$artistAlbums = [];
$artistAlbumsAsHTML = "";

/**
 * Initialize the data base
 *
 * @param string $host name of the host
 * @param string $dbname name of the data base
 * @param string $username name of the user
 * @param string $password password of the data base
 **/
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

/**
 * Query artist information
 *
 * This query retrieves the artist name, its cover, its bio,
 * the number of monthly listeners.
 **/
try {
    $artistInfos = $db->executeQuery(<<<SQL
    SELECT *
    FROM artist
    WHERE id = :idArtist
    SQL, ["idArtist" => $idArtist]);

    // redirection to error page if idArtist doesn't exist
    if (sizeof($artistInfos) == 0) {
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

// converting the result into a simple array
$artistInfosInArray = $artistInfos[0];

// storing artist information in variables
$artistName = $artistInfosInArray['name'];
$artistCover = $artistInfosInArray['cover'];
$artistBio = $artistInfosInArray['biography'];
$artistMonthlyListeners = $artistInfosInArray['monthly_listeners'];

// formatting monthly listeners with .k and .M
$artistMonthlyListenersInLetter = numberWithLetter($artistMonthlyListeners);

// generating the HTML block containing artist information
$artistInfoAsHTML = <<<HTML
    <header class="artist-header">
        <img src="$artistCover" alt="Photo de l'artiste: $artistName" class="artist-cover-large">
        <div class="artist-details">
            <h1 class="artist-name">$artistName</h1>
            <p class="monthly-listeners">
                <span class="listener-count">$artistMonthlyListenersInLetter</span>
                <span class="listener-label">d'auditeurs mensuels</span>
            </p>
            <p class="artist-bio">$artistBio</p>
        </div>
    </header>
HTML;


/**
 * Query the top 5 songs of the album
 *
 * This query returns each song name, duration, note, and album cover,
 * ordered by song note in descending order.
 **/
try {
    $artistTop5Songs = $db->executeQuery(<<<SQL
    SELECT
        song.name AS song_name,
        song.duration AS song_duration,
        song.note AS song_note,
        album.cover AS album_cover,
        album.id AS album_id
    FROM song
    INNER JOIN album ON album.id = song.album_id
    WHERE song.artist_id = :idArtist
    ORDER BY song.note DESC
    LIMIT 5
SQL, ["idArtist" => $idArtist]);

} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

// generating HTML for each top 5 song
foreach ($artistTop5Songs as $song) {
    $songName = $song['song_name'];
    $songDuration = $song['song_duration'];
    $songNote = $song['song_note'];
    $albumCover = $song['album_cover'];
    $albumId = $song['album_id'];

    // convert duration into MM:SS format
    $songDurationInMMSS = timeInMMSS($songDuration);

    $artistTop5SongsAsHTML .= <<<HTML
        <div class="track-item">
            <div class="track-info">
                <a href="album.php?id=$albumId" class="track-link">
                    <img src="$albumCover" alt="Pochette de l'album" class="track-album-cover">
                    <span class="track-name">$songName</span>
                </a>
            </div>
            <div class="track-details">
                <span class="track-duration">$songDurationInMMSS</span>
                <span class="track-note">Note: $songNote/5</span>
            </div>
        </div>
    HTML;
}

/**
 * Query all the albums of the current artist
 *
 * This query returns each album name, cover, and release date,
 * ordered by release date in descending order.
 **/
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

// generating HTML for each song of the album
foreach ($artistAlbums as $album) {
    $albumId = $album['album_id'];
    $albumName = $album['album_name'];
    $albumCover = $album['album_cover'];
    $albumReleaseDate = $album['album_release_date'];

    // formatting release date as DD/MM/YYYY
    $albumReleaseDateInDMY = dateInDMY($albumReleaseDate);

    // generating the HTML block containing album information
    $artistAlbumsAsHTML .= <<<HTML
        <div class="card-item album">
            <a href="album.php?id=$albumId">
                <img src="$albumCover" alt="Pochette de l'album: $albumName">
                <h5>$albumName</h5>
                <p>$albumReleaseDateInDMY</p>
            </a>
        </div>
    HTML;
}

// final HTML structure of the page
$html = <<< HTML
<div class="page-container">
    <a href="index.php" class="back-link">← Retour à l'accueil</a>
    $artistInfoAsHTML
    <div class="content-section">
        <h2>Morceaux Populaires</h2>
        <div class="track-list">
            $artistTop5SongsAsHTML
        </div>
    </div>
    <div class="content-section">
        <h2>Albums</h2>
        <div class="card-grid">
            $artistAlbumsAsHTML
        </div>
    </div>
</div>
HTML;

// displaying the page using HTMLPage class
echo (new HTMLPage(title: "Lowify - $artistName"))
    ->addContent($html)
    ->addStylesheet("inc/style.css")
    ->render();