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
$isLikedSong = 1;

$error = "error.php?message=Erreur";

$likedSongs = [];
$likedSongsAsHTML = "";

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
 * Query liked songs information
 *
 * This query retrieves all name, note, and duration of liked songs
 **/
try {
    $likedSongs = $db->executeQuery(<<<SQL
    SELECT
        song.name AS song_name,
        song.duration AS song_duration,
        song.note AS song_note,
        song.is_liked AS song_is_liked,
        song.id AS song_id,
        album.name AS album_name,
        album.id AS album_id,
        artist.name AS artist_name,
        artist.id AS artist_id
    FROM song
    INNER JOIN album ON song.album_id = album.id
    INNER JOIN artist ON song.artist_id = artist.id
    WHERE song.is_liked = :isLikedSong
    ORDER BY artist.name ASC
SQL, ["isLikedSong" => $isLikedSong]);
} catch (PDOException $ex) {
    header("Location: $error");
    exit;
}

// Return message if none of the song is liked
// or generating HTML for each song
if (sizeof($likedSongs) == 0) {
    $likedSongsAsHTML .= <<<HTML
        <p class="no-result">Votre liste de chanson likés est encore vide.</p>
    HTML;
} else {
    $likedSongsAsHTML .= <<<HTML
        <div class="track-list track-list-search">
    HTML;

    foreach ($likedSongs as $song) {
        $songName = $song['song_name'];
        $songDuration = $song['song_duration'];
        $songNote = $song['song_note'];
        $songIsLiked = $song['song_is_liked'];
        $songId = $song['song_id'];
        $albumName = $song['album_name'];
        $albumId = $song['album_id'];
        $artistName = $song['artist_name'];
        $artistId = $song['artist_id'];

        $songDurationInMMSS = timeInMMSS($songDuration);
        $songNoteFormatted = noteFormatted($songNote);

        $isLiked = $song['song_is_liked'] == 0 ? '♡' : '♥';

        $likedSongsAsHTML .= <<<HTML
        <div class="track-item track-item-album">
            <div class="track-info">
                <div class="track-text-info">
                    <span class="track-name">$songName</span>
                    <span class="track-artist">
                        <a href="artist.php?id=$artistId" title="$artistName - Détails de l'artiste">$artistName</a>
                        <span class="meta-separator"> • </span>
                        <a href="album.php?id=$albumId" title="$albumName - Détails de l'album">$albumName</a>
                    </span>
                </div>
            </div>
            <div class="track-details">
                <a href="like_song.php?id=$songId" title="Like/Unlike la chanson">$isLiked</a>
                <span class="track-duration">$songDurationInMMSS</span>
                <span class="track-note-small">Note: $songNoteFormatted</span>
            </div>
        </div>
        HTML;
    }
    $likedSongsAsHTML .= <<<HTML
        </div>
    HTML;
}

// final HTML structure of the page
$html = <<< HTML
<div class="page-container liked-songs-page">
<a href="index.php" class="back-link" title="Retour à l'accueil">← Retour à l'accueil</a>
    <h1>Lowify - Titres likés</h1>
    
    <div class="content-section">
        <div class="card-grid">
            $likedSongsAsHTML
        </div>
    </div>
</div>
HTML;

// displaying the page using HTMLPage class
echo (new HTMLPage(title: "Lowify - Titres likés"))
    ->addContent($html)
    ->addHead('<meta charset="utf-8">')
    ->addHead('<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">')
    ->addStylesheet("inc/style.css")
    ->render();