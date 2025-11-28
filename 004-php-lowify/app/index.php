<?php
// files included
require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

$host = "mysql";
$dbname = "lowify";
$username = "lowify";
$password = "lowifypassword";

$db = null;

$top5Artists = [];
$top5ArtistsAsHTML = "";
$top5RecentAlbums = [];
$top5RecentAlbumsAsHTML = "";
$top5NotationAlbums = [];
$top5NotationAlbumsAsHTML = "";

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
 * Query top 5 artist most listened information
 *
 * This query retrieves the artist name, its cover, id,
 * and order by monthly listeners.
 **/
try {
    $top5Artists = $db->executeQuery(<<<SQL
    SELECT
        name,
        id,
        cover,
        monthly_listeners
    FROM artist
    ORDER BY monthly_listeners DESC
    LIMIT 5
SQL);
} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

// generating HTML for each top 5 artist
foreach ($top5Artists as $artist) {
    $artistId = $artist['id'];
    $artistName = $artist['name'];
    $artistCover = $artist['cover'];

    $top5ArtistsAsHTML .= <<<HTML
        <div>
        <a href="artist.php?id=$artistId">
            <img src="$artistCover" alt="Photo de l'artiste">
            <p>$artistName</p>
        </a>
        </div>
HTML;
}

/**
 * Query top 5 album most recent
 *
 * This query retrieves the album name, its cover, its release date,
 * the name of his artist, and order by release date.
 **/
try {
    $top5RecentAlbums = $db->executeQuery(<<<SQL
    SELECT
        album.name AS album_name,
        album.id AS album_id,
        album.cover AS album_cover,
        album.release_date AS album_release_date,
        artist.name AS artist_name,
        artist.id AS artist_id
    FROM album
    INNER JOIN artist ON album.artist_id = artist.id
    ORDER BY album.release_date DESC
    LIMIT 5
SQL);
} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

// generating HTML for each album of the top 5
foreach ($top5RecentAlbums as $album) {
    $albumId = $album['album_id'];
    $albumName = $album['album_name'];
    $albumCover = $album['album_cover'];
    $artistName = $album['artist_name'];
    $artistId = $album['artist_id'];

    $top5RecentAlbumsAsHTML .= <<<HTML
        <div>
        <a href="album.php?id=$albumId">
            <img src="$albumCover" alt="Photo de l'album">
            <p>$albumName - <a href=artist.php?id=$artistId"">$artistName</a></p>
        </a>
        </div>
HTML;
}

/**
 * Query top 5 album with best notes
 *
 * This query retrieves the album name, its cover, its release date,
 * the name of his artist, and order by notes descendent.
 **/
try {
    $top5NotationAlbums = $db->executeQuery(<<<SQL
    SELECT
        album.name AS album_name,
        album.id AS album_id,
        album.cover AS album_cover,
        album.release_date AS album_release_date,
        artist.name AS artist_name,
        artist.id AS artist_id,
        AVG(song.note) AS song_avg_note
    FROM album
    INNER JOIN artist ON album.artist_id = artist.id
    INNER JOIN song ON album.id = song.album_id
    GROUP BY album.id, album.name, album.cover, album.release_date, artist.name, artist.id
    ORDER BY song_avg_note DESC
    LIMIT 5
SQL);
} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

// generating HTML for each album of the top 5 notation
foreach ($top5NotationAlbums as $album) {
    $albumId = $album['album_id'];
    $albumName = $album['album_name'];
    $albumCover = $album['album_cover'];
    $artistName = $album['artist_name'];
    $artistId = $album['artist_id'];

    $top5NotationAlbumsAsHTML .= <<<HTML
        <div>
        <a href="album.php?id=$albumId">
            <img src="$albumCover" alt="Photo de l'album">
            <p>$albumName - <a href=artist.php?id=$artistId"">$artistName</a></p>
        </a>
        </div>
HTML;
}

// final HTML structure of the page
$html = <<< HTML
<h1>Accueil</h1>
<div>
<form action="search.php" method="POST">
<input type="search" id="site-search" name="search" />
<button>Rechercher</button>
</form>
</div>
<h2>Top 5 des artistes les plus populaires</h2>
<div>
$top5ArtistsAsHTML
</div>
<h2>Top 5 des albums les plus récents</h2>
<div>
$top5RecentAlbumsAsHTML
</div>
<h2>Top 5 des albums les mieux notés</h2>
<div>
$top5NotationAlbumsAsHTML
</div>
HTML;

// displaying the page using HTMLPage class
echo (new HTMLPage(title: "Lowify"))
    ->addContent($html)
    ->render();