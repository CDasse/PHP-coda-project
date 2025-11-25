<?php
$playerChoice = $_GET["player"] ?? "Faites votre choix !";

$arrayChoice = ["Pierre", "Feuille", "Ciseaux", "Lézard", "Spock"];
$phpChoice = array_rand(array_flip($arrayChoice), 1);

$result = "A vous de jouer !";

if ($playerChoice === "Faites votre choix !") {
    $phpChoice = "Il n'attend que vous ...";
    $result = "Bah rien pour l'instant, tu n'as pas encore joué ^^";
}

$gagnePierre = ($playerChoice === "Pierre" && $phpChoice === "Ciseaux") || ($playerChoice === "Pierre" && $phpChoice === "Lézard");
$gagneFeuille = ($playerChoice === "Feuille" && $phpChoice === "Pierre") || ($playerChoice === "Feuille" && $phpChoice === "Spock");
$gagneCiseaux = ($playerChoice === "Ciseaux" && $phpChoice === "Feuille") || ($playerChoice === "Ciseaux" && $phpChoice === "Lézard");
$gagneLezard = ($playerChoice === "Lézard" && $phpChoice === "Feuille") || ($playerChoice === "Lézard" && $phpChoice === "Spock");
$gagneSpock = ($playerChoice === "Spock" && $phpChoice === "Pierre") || ($playerChoice === "Spock" && $phpChoice === "Ciseaux");

$perduPierre = ($playerChoice === "Pierre" && $phpChoice === "Feuille") || ($playerChoice === "Pierre" && $phpChoice === "Spock");
$perduFeuille = ($playerChoice === "Feuille" && $phpChoice === "Ciseaux") || ($playerChoice === "Feuille" && $phpChoice === "Lézard");
$perduCiseaux = ($playerChoice === "Ciseaux" && $phpChoice === "Pierre") || ($playerChoice === "Ciseaux" && $phpChoice === "Spock");
$perduLezard = ($playerChoice === "Lézard" && $phpChoice === "Pierre") || ($playerChoice === "Lézard" && $phpChoice === "Ciseaux");
$perduSpock = ($playerChoice === "Spock" && $phpChoice === "Feuille") || ($playerChoice === "Spock" && $phpChoice === "Lézard");

$egalitePierre = $playerChoice === "Pierre" && $phpChoice === "Pierre";
$egaliteFeuille = $playerChoice === "Feuille" && $phpChoice === "Feuille";
$egaliteCiseaux = $playerChoice === "Ciseaux" && $phpChoice === "Ciseaux";
$egaliteLezard = $playerChoice === "Lézard" && $phpChoice === "Lézard";
$egaliteSpock = $playerChoice === "Spock" && $phpChoice === "Spock";

if ($egalitePierre || $egaliteFeuille || $egaliteCiseaux || $egaliteLezard || $egaliteSpock) {
    $result = "Egalité";
} else if ($gagnePierre || $gagneFeuille || $gagneCiseaux || $gagneLezard || $gagneSpock) {
    $result = "Gagné !";
} else if ($perduPierre || $perduFeuille || $perduCiseaux || $perduLezard || $perduSpock) {
    $result = "Perdu :-(";
}

$html =<<< HTML
    <!doctype html>
    <html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Jeu Pierre, Feuilles, Ciseaux</title>
    <style>
        h1,
        div {
            text-align: center;
        }
        
        h1 {
            padding: 20px;
        }
        
        div {
            display: flex;
            flex-direction: row;
            justify-content: space-around;
            margin: 10px 0;
        }
        
        p {
            padding: 5px;
            line-height: 25px;
        }
        
        span {
            color: blue;
        }
        
        a {
        text-decoration: none;
        border: 2px solid black;
        background-color: blue;
        color: white;
        padding: 3px;
        }
        
        section {
            text-align: center;
        }
        
        .row {
        display: flex;
        flex-direction: column;
        }
    </style>
    </head>
    <body>
        <h1>Jeu Pierre, Feuilles, Ciseaux</h1>
        <div>
            <p>Choix du joueur : <br>
            <span>$playerChoice</span> </p>
            <p>Choix de PHP : <br>
            <span>$phpChoice</span> </p>
        </div>
        <div>
            <p>Résultat : <br>
            <span>$result</span> </p>
        </div>
        <div class="row">
            <p>Statistiques :</p>
            <p> - Nombre de parties :</p>
            <p> - Nombre de victoires :</p>
            <p> - Nombre de défaites :</p>
            <p> - Nombre d'égalités :</p>
        </div>
        <section>
        <a href="?player=Pierre">Pierre</a>
        <a href="?player=Feuille">Feuille</a>
        <a href="?player=Ciseaux">Ciseaux</a>
        <a href="?player=Lézard">Lézard</a>
        <a href="?player=Spock">Spock</a>
        <a href="http://localhost:80">Réinitialiser</a>
        </section>
    </body>
    </html>
HTML;

echo $html;