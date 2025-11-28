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

$message = $_GET["message"];

$html = <<< HTML
<h1>Artiste inconnu</h1>
<div>
    <p>$message</p>
    <p>L'artiste demandé n'a malheureusement pas été trouvé.</p>
    <a href="artists.php">Retour à l'accueil</a>
</div>
HTML;

echo (new HTMLPage(title: "Lowify - Artiste inconnu"))
    ->addContent($html)
    ->render();