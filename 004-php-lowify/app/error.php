<?php

// files included
require_once 'inc/page.inc.php';

$message = $_GET["message"];

$html = <<< HTML
<h1>$message</h1>
    <div>
    <p>L'artiste demandé n'a malheureusement pas été trouvé.</p>
    <a href="artists.php">Retour à l'accueil</a>
</div>
HTML;

echo (new HTMLPage(title: "Lowify - Artiste inconnu"))
    ->addContent($html)
    ->render();