<?php

// files included
require_once 'inc/page.inc.php';

$message = $_GET["message"];

// final HTML structure of the page
$html = <<< HTML
<h1>$message</h1>
    <div>
    <p>L'artiste demandé n'a malheureusement pas été trouvé.</p>
    <a href="index.php">Retour à l'accueil</a>
</div>
HTML;

// displaying the page using HTMLPage class
echo (new HTMLPage(title: "Lowify - $message"))
    ->addContent($html)
    ->render();