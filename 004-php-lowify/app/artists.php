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

// table of artists
$allArtists = [];

// extract artists data from data base
try {
    $allArtists = $db->executeQuery(<<<SQL
    SELECT
        id,
        name,
        cover
    FROM lowify.artist
SQL);
} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}


$artistesAsHTML = "";

//edit HTML structure form each artist
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

$html = <<< HTML
<h1>Lowify - Artistes</h1>
<div>
{$artistesAsHTML}
</div>
HTML;

echo (new HTMLPage(title: "Lowify - Artistes"))
    ->addContent($html)
    ->render();