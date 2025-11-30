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
$error = "error.php?message=Chanson inconnue";

$idSong = $_GET["id"];

$songInfos = [];
$newValueIsLike = null;

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
 * Query song information
 *
 * This query retrieves the song like status by its id.
 **/
try {
    $songInfos = $db->executeQuery(<<<SQL
    SELECT is_liked AS is_liked_song
    FROM song
    WHERE id = :idSong
    SQL, ["idSong" => $idSong]);

    // redirection to error page if idArtist doesn't exist
    if (sizeof($songInfos) == 0) {
        header("Location: $error");
        exit;
    }

} catch (PDOException $ex) {
    header("Location: $error");
    exit;
}

// converting the result into a simple array
$songInfosInArray = $songInfos[0];

// storing song information in variables
$songIsLike = $songInfosInArray['is_liked_song'];

// modifie the value of is_like
if ($songIsLike == 1) {
    $newValueIsLike = 0;
} else if ($songIsLike == 0) {
    $newValueIsLike = 1;
}

/**
 * Query modifie value of is_liked
 *
 * This query modifie the value of is_liked in the data base.
 **/
$db->executeQuery(<<<SQL
    UPDATE song
    SET is_liked = :newValueIsLike
    WHERE id = :idSong
    SQL, ["idSong" => $idSong , "newValueIsLike" => $newValueIsLike]);

// return to the previous web page
header('Location: ' . $_SERVER['HTTP_REFERER']);