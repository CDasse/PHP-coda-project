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

$allArtists = [];
$artistesAsHTML = "";

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
 * Query artists information
 *
 * This query retrieves all artist name, cover, and id
 **/
try {
    $allArtists = $db->executeQuery(<<<SQL
    SELECT
        id,
        name,
        cover
    FROM artist
SQL);
} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

// generating HTML for each artist
foreach ($allArtists as $artist) {
    $artistId = $artist['id'];
    $artistName = $artist['name'];
    $artistCover = $artist['cover'];

    $artistesAsHTML .= <<<HTML
            <div>
                <a href="artist.php?id=$artistId">
                    <div>
                        <img src="$artistCover" alt="Photo de l'artiste">
                        <div>
                            <h5>$artistName</h5>
                        </div>
                    </div>
                </a>
            </div>
HTML;
}

// final HTML structure of the page
$html = <<< HTML
<h1>Lowify - Artistes</h1>
<div>
{$artistesAsHTML}
</div>
HTML;

// displaying the page using HTMLPage class
echo (new HTMLPage(title: "Lowify - Artistes"))
    ->addContent($html)
    ->render();