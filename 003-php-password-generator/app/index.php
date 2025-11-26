<?php
$selected = $_POST["size"] ?? 12;
$useAlphaMin = $_POST["use-alpha-min"] ?? 0;
$useAlphaMaj = $_POST["use-alpha-maj"] ?? 0;
$useNum = $_POST["use-num"] ?? 0;
$useSymbols = $_POST["use-symbols"] ?? 0;

function isChecked($nameCheckbox) : string {
    $checked = "";
    if ($nameCheckbox === "1") {
        $checked = "checked";
    }
    return $checked;
}

$isUseAlphaMinChecked = isChecked($useAlphaMin);
$isUseAlphaMajChecked = isChecked($useAlphaMaj);
$isUseNumChecked = isChecked($useNum);
$isUseSymbolsChecked = isChecked($useSymbols);


function generateSelectOptions(int $selected = 12): string {
    $html = "";

    $options = range(8, 42);

    foreach ($options as $value) {
        $attribute = "";
        if ((int) $value == (int) $selected) {
            $attribute = "selected";
        }

        $html .= "<option $attribute value=\"$value\">$value</option>";
    }
    return $html;
}

$optionsGenerated = generateSelectOptions($selected);

function takeRandom(string $subject): string {
    $index = random_int(0, strlen($subject) - 1);

    $randomChar = $subject[$index];

    return $randomChar;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $generated = generatePassword($selected, $useAlphaMin, $useAlphaMaj, $useNum, $useSymbols);
} else {
    $useAlphaMin = 1;
    $useAlphaMaj = 1;
    $useNum = 1;
    $useSymbols = 1;
}

function generatePassword(
    int $size,
    bool $useAlphaMin,
    bool $useAlphaMaj,
    bool $useNum,
    bool $useSymbols
): string {
    $password = "";

    $sequences = [];

    if ($useAlphaMin == 1) {
        $sequences[] = "abcdefghijklmnopqrstuvwxz";
    }

    if ($useAlphaMaj == 1) {
        $sequences[] = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    }

    if ($useNum == 1) {
        $sequences[] = "0123456789";
    }

    if ($useSymbols == 1) {
        $sequences[] = "!@#$%^&*";
    }

    $limitBoucle = $size - ($useAlphaMin + $useAlphaMaj + $useNum + $useSymbols);

    if ($useAlphaMaj == 1) {
        $password .= takeRandom($sequences[0]);
    }

    if ($useAlphaMin == 1) {
        $password .= takeRandom($sequences[1]);
    }

    if ($useNum == 1) {
        $password .= takeRandom($sequences[2]);
    }

    if ($useSymbols == 1) {
        $password .= takeRandom($sequences[3]);
    }

    for($i = 1; $i < $limitBoucle; $i++) {
        $sequenceAleatoire = $sequences[rand(0,sizeof($sequences) - 1)];
        $password .= takeRandom($sequenceAleatoire);
    }

    $password = str_shuffle($password);

    return $password;
}

$gereratedPassword = generatePassword($selected, $useAlphaMin, $useAlphaMaj, $useNum, $useSymbols);

$page = <<< HTML
<!doctype html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Générateur de mots de passe</title>
    </head>
    <body>
        <h1>Générateur de mots de passe</h1>
        <form method="POST" action="/">
            <div>
            <p>Mot de passe :</p>
            <p>$gereratedPassword</p>
            </div>
            <div>
                <label for="size" class="form-label">Taille</label>
                <select id="size" class="form-select" aria-label="Default select example" name="size">
                    $optionsGenerated
                </select>
            </div>
            <div class="">
                <input class="" type="checkbox" value="1" id="use-alpha-min" name="use-alpha-min" $isUseAlphaMinChecked>
                <label class="" for="use-alpha-min">Utiliser les lettres minuscules (a-z)</label>
            </div>
            <div class="">
                <input class="" type="checkbox" value="1" id="use-alpha-maj" name="use-alpha-maj" $isUseAlphaMajChecked>
                <label class="" for="use-alpha-maj">Utiliser les lettres majuscules (A-Z)</label>
            </div>
            <div class="">
                <input class="" type="checkbox" value="1" id="use-num" name="use-num"  $isUseNumChecked>
                <label class="" for="use-num">Utiliser les chiffres (0-9)</label>
            </div>
            <div class="">
                <input class="" type="checkbox" value="1" id="use-symbols" name="use-symbols" $isUseSymbolsChecked>
                <label class="" for="use-symbols">Utiliser les symboles (!@#$%^&*())</label>
            </div>
            <div class="">
                <button type="submit" class="">Générer !</button>
            </div>
        </form>
    </body>
</html>
HTML;

echo $page;
