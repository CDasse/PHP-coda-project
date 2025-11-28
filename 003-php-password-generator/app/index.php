<?php
// get choices from the user
$size = $_POST["size"] ?? 12;
$useAlphaMin = $_POST["use-alpha-min"] ?? 0;
$useAlphaMaj = $_POST["use-alpha-maj"] ?? 0;
$useNum = $_POST["use-num"] ?? 0;
$useSymbols = $_POST["use-symbols"] ?? 0;


// Keep the checkbox checked if selected by the user.
function isChecked($nameCheckbox) : string {
    $checked = "";
    if ($nameCheckbox === "1") {
        $checked = "checked";
    }
    return $checked;
}

// verify the status of checkbox
$isUseAlphaMinChecked = isChecked($useAlphaMin);
$isUseAlphaMajChecked = isChecked($useAlphaMaj);
$isUseNumChecked = isChecked($useNum);
$isUseSymbolsChecked = isChecked($useSymbols);

//create options for size in html
function generateSelectOptions(int $size = 12): string {
    $html = "";

    $options = range(8, 42);

    foreach ($options as $value) {
        $attribute = "";
        if ((int) $value == (int) $size) {
            $attribute = "selected";
        }

        $html .= "<option $attribute value=\"$value\">$value</option>";
    }
    return $html;
}

$optionsGenerated = generateSelectOptions($size);

// take a random char in a string
function takeRandom(string $subject): string {
    $index = random_int(0, strlen($subject) - 1);

    $randomChar = $subject[$index];

    return $randomChar;
}

$generated = "";
// default status of checkbox
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $generated = generatePassword($size, $useAlphaMin, $useAlphaMaj, $useNum, $useSymbols);
} else {
    $useAlphaMin = 1;
    $useAlphaMaj = 1;
    $useNum = 1;
    $useSymbols = 1;

    $generated = generatePassword($size, $useAlphaMin, $useAlphaMaj, $useNum, $useSymbols);
}

// generation of password
function generatePassword(
    int $size,
    bool $useAlphaMin,
    bool $useAlphaMaj,
    bool $useNum,
    bool $useSymbols
): string {

    // manage error if none of the checkbox are selected
    if ($useAlphaMin == 0 &&
    $useAlphaMaj == 0 &&
    $useNum == 0 &&
    $useSymbols == 0) {
        return "Choisissez au moins un type de caractères.";
    }
    $password = "";

    $sequences = [];

    if ($useAlphaMaj == 1) {
        $sequences["maj"] = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    }

    if ($useAlphaMin == 1) {
        $sequences["min"] = "abcdefghijklmnopqrstuvwxyz";
    }

    if ($useNum == 1) {
        $sequences["num"] = "0123456789";
    }

    if ($useSymbols == 1) {
        $sequences["symbols"] = "!@#$%^&*";
    }


    // add at least one char of every checkbox checked
    if ($useAlphaMaj == 1) {
        $password .= takeRandom($sequences["maj"]);
    }
    if ($useAlphaMin == 1) {
        $password .= takeRandom($sequences["min"]);
    }
    if ($useNum == 1) {
        $password .= takeRandom($sequences["num"]);
    }
    if ($useSymbols == 1) {
        $password .= takeRandom($sequences["symbols"]);
    }

    // calculate size of password without first char initialized
    $limitBoucle = $size - ($useAlphaMin + $useAlphaMaj + $useNum + $useSymbols);

    // complete the password
    for($i = 0; $i < $limitBoucle; $i++) {
        // need to creat an array with number for index -> rand
        $values = array_values($sequences);
        $randomSequence = $values[rand(0, count($values) - 1)];
        $password .= takeRandom($randomSequence);
    }

    // shuffle the password
    $password = str_shuffle($password);

    return $password;
}

$page = <<< HTML
<!doctype html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Générateur de mots de passe</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: "Inter", Arial, sans-serif;
            }
            
            body {
                background: #f5f7fa;
                padding: 40px;
                display: flex;
                justify-content: center;
            }
            
            .container {
                width: 100%;
                max-width: 620px;
            }
            
            h1 {
                text-align: center;
                font-size: 2em;
                margin-bottom: 25px;
                color: #222;
                font-weight: 700;
            }
            
            .section-title {
                font-weight: 600;
                font-size: 1em;
                margin-bottom: 6px;
                display: block;
            }
            
            form {
                background: #fff;
                padding: 30px 40px;
                border-radius: 14px;
                box-shadow: 0 12px 25px rgba(0,0,0,0.08);
            }
            
            .password-box {
                padding: 15px;
                background: #f0f3f7;
                border: 2px solid #d1d6dd;
                border-radius: 10px;
                margin-bottom: 25px;
                font-family: "Courier New", monospace;
                font-size: 1.15em;
                word-break: break-all;
                user-select: all;
            }
            
            select {
                width: 100%;
                padding: 10px;
                border-radius: 8px;
                border: 1px solid #ddd;
                font-size: 1em;
                margin-bottom: 20px;
            }
            
            .checkbox-group {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 12px;
            }
            
            input[type="checkbox"] {
                width: 18px;
                height: 18px;
                accent-color: #4c8bff;
            }
            
            button {
                width: 100%;
                padding: 14px;
                background: linear-gradient(135deg,#4c8bff,#2a6eed);
                border: none;
                color: #fff;
                font-size: 1.1em;
                font-weight: 600;
                border-radius: 10px;
                cursor: pointer;
                margin-top: 10px;
            }
            
            button:hover {
                background: linear-gradient(135deg,#2a6eed,#4c8bff);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Générateur de mots de passe</h1>
            <form method="POST" action="/">
                <div>
                <label class="section-title">Mot de passe :</label>
                <div class="password-box">
                    $generated
                </div>
                </div>
                <div>
                    <label for="size" class="form-label">Taille</label>
                    <select id="size" class="form-select" aria-label="Default select example" name="size">
                        $optionsGenerated
                    </select>
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" value="1" id="use-alpha-min" name="use-alpha-min" $isUseAlphaMinChecked>
                    <label for="use-alpha-min">Utiliser les lettres minuscules (a-z)</label>
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" value="1" id="use-alpha-maj" name="use-alpha-maj" $isUseAlphaMajChecked>
                    <label for="use-alpha-maj">Utiliser les lettres majuscules (A-Z)</label>
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" value="1" id="use-num" name="use-num" $isUseNumChecked>
                    <label for="use-num">Utiliser les chiffres (0-9)</label>
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" value="1" id="use-symbols" name="use-symbols" $isUseSymbolsChecked>
                    <label for="use-symbols">Utiliser les symboles (!@#$%^&*())</label>
                </div>
                <div>
                    <button type="submit">Générer !</button>
                </div>
            </form>
        </div>
    </body>
</html>
HTML;

echo $page;
