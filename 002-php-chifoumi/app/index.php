<?php
// Recuperation du choix du joueur : pierre / feuille / ciseaux
$playerChoice = $_GET["player"] ?? "Faites votre choix !";

// tableau des possibilité
$arrayChoice = ["Pierre", "Feuille", "Ciseaux", "Lézard", "Spock"];

// déclaration de la variable qui affichera le resultat
$result = "A vous de jouer !";

// choix aléatoire de php dans le tableau $arrayChoice
$phpChoice = array_rand(array_flip($arrayChoice), 1);

// initialisation de l'affichage (choix du joueur et php / resultat)
if ($playerChoice === "Faites votre choix !") {
    $phpChoice = "Il n'attend que vous ...";
    $result = "Bah rien pour l'instant, tu n'as pas encore joué ^^";
}

// déclaration des différents cas où le joueur gagne
$gagnePierre = ($playerChoice === "Pierre" && $phpChoice === "Ciseaux") || ($playerChoice === "Pierre" && $phpChoice === "Lézard");
$gagneFeuille = ($playerChoice === "Feuille" && $phpChoice === "Pierre") || ($playerChoice === "Feuille" && $phpChoice === "Spock");
$gagneCiseaux = ($playerChoice === "Ciseaux" && $phpChoice === "Feuille") || ($playerChoice === "Ciseaux" && $phpChoice === "Lézard");
$gagneLezard = ($playerChoice === "Lézard" && $phpChoice === "Feuille") || ($playerChoice === "Lézard" && $phpChoice === "Spock");
$gagneSpock = ($playerChoice === "Spock" && $phpChoice === "Pierre") || ($playerChoice === "Spock" && $phpChoice === "Ciseaux");

// déclaration des différents cas où le joueur perd
$perduPierre = ($playerChoice === "Pierre" && $phpChoice === "Feuille") || ($playerChoice === "Pierre" && $phpChoice === "Spock");
$perduFeuille = ($playerChoice === "Feuille" && $phpChoice === "Ciseaux") || ($playerChoice === "Feuille" && $phpChoice === "Lézard");
$perduCiseaux = ($playerChoice === "Ciseaux" && $phpChoice === "Pierre") || ($playerChoice === "Ciseaux" && $phpChoice === "Spock");
$perduLezard = ($playerChoice === "Lézard" && $phpChoice === "Pierre") || ($playerChoice === "Lézard" && $phpChoice === "Ciseaux");
$perduSpock = ($playerChoice === "Spock" && $phpChoice === "Feuille") || ($playerChoice === "Spock" && $phpChoice === "Lézard");

// déclaration des différents cas où il y a égalité
$egalitePierre = $playerChoice === "Pierre" && $phpChoice === "Pierre";
$egaliteFeuille = $playerChoice === "Feuille" && $phpChoice === "Feuille";
$egaliteCiseaux = $playerChoice === "Ciseaux" && $phpChoice === "Ciseaux";
$egaliteLezard = $playerChoice === "Lézard" && $phpChoice === "Lézard";
$egaliteSpock = $playerChoice === "Spock" && $phpChoice === "Spock";

// affichage du resultat en fonction du choix du joueur et de php
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Courier New", monospace;
        }
        
        body {
            background-color: #0a0a0a;
            color: #66ff66;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 50px 20px;
            min-height: 100vh;
        }
        
        h1 {
            font-size: 2.5em;
            color: #66ff66;
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #66ff66;
            padding-bottom: 10px;
        }
        
        div {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin-bottom: 35px;
        }
        
        section {
            background-color: #101010;
            border: 2px solid #33cc33;
            padding: 20px 25px;
            border-radius: 10px;
            min-width: 200px;
            text-align: center;
        }
        
        .stat {
        margin: 20px;
        }
        
        span {
            display: block;
            font-weight: bold;
            font-size: 1.2em;
            margin-top: 8px;
        }
        
        a {
            text-decoration: none;
            margin: 6px;
            display: inline-block;
            padding: 12px 20px;
            border-radius: 8px;
            border: 2px solid #33cc33;
            color: #33cc33;
            font-weight: bold;
            font-size: 1em;
            text-transform: uppercase;
            transition: all 0.2s ease;
        }
        
        a:hover {
            background-color: #33cc33;
            color: #0a0a0a;
        }
        
        .cta {
        margin: 20px;
        max-width: 700px;
        }
        
        section:nth-of-type(4) p {
            font-size: 1em;
            color: #66ff66;
            margin: 6px 0;
        }
        
        @media (max-width: 600px) {
            div {
                flex-direction: column;
                gap: 20px;
            }
        
            section {
                min-width: 100%;
            }
        }
    </style>
    </head>
    <body>
        <h1>Jeu Pierre, Feuilles, Ciseaux</h1>
        <div>
        <section>
            <p>Choix du joueur : <br>
            <span>$playerChoice</span> </p>
        </section>
        <section>
            <p>Choix de PHP : <br>
            <span>$phpChoice</span> </p>
        </section>
        </div>
        <section>
            <p>Résultat : <br>
            <span>$result</span> </p>
        </section>
<!--        <section class="stat">-->
<!--            <p>Statistiques :</p>-->
<!--            <p> - Nombre de parties : </p>-->
<!--            <p> - Nombre de victoires :</p>-->
<!--            <p> - Nombre de défaites :</p>-->
<!--            <p> - Nombre d'égalités :</p>-->
<!--        </section>-->
        <section class="cta">
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