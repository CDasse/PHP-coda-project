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

$top5Artists = [];

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

$top5ArtistsAsHTML = "";

foreach ($top5Artists as $artist) {
    $artistId = $artist['id'];
    $artistName = $artist['name'];
    $artistCover = $artist['cover'];

    $top5ArtistsAsHTML .= <<<HTML
        <div>
        <a href=artist.php?id=$artistId"">
            <img src="$artistCover" alt="Photo de l'artiste">
            <p>$artistName</p>
        </a>
        </div>
HTML;
}

$html = <<< HTML
<h1>Accueil</h1>
<h2>Top 5 des artistes les plus populaires</h2>
<div>
$top5ArtistsAsHTML
</div>
<h2>Top 5 des albums les plus récents</h2>
<div>

</div>
<h2>Top 5 des albums les mieux notés</h2>
<div>

</div>
HTML;

echo (new HTMLPage(title: "Lowify"))
    ->addContent($html)
    ->render();