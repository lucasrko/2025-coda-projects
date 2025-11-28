<?php

/**
 * Generates a string of HTML <option> elements for a select dropdown, with one option marked as selected.
 *
 * @param int|string $selected The value to be marked as selected in the dropdown.
 * @return string The generated HTML string containing <option> elements.
 */
function generateSelectOptions($selected = 12): string
{
    // on initialise une variable html vide
    $html = "";

    // utilisation de la fonction range pour générer un tableau de valeurs
    $options = range(8, 42);

    // pour chaque nombre de 8 à 42
    foreach ($options as $value) {
        // si le nombre courant est celui sélectionné, on ajoute l'attribut selected à l'option
        $attribute = "";
        if ((int) $value == (int) $selected) {
            $attribute = "selected";
        }

        // on crée une option avec l'attribut et la valeur'
        $html .= "<option {$attribute} value=\"{$value}\">{$value}</option>";
    }

    return $html;
}

/**
 * Selects and returns a random character from the given string.
 *
 * @param string $subject The string from which a random character will be selected.
 * @return string A single randomly selected character from the input string.
 * @see https://www.php.net/manual/fr/function.random-int.php
 * @see https://www.php.net/manual/fr/function.strlen.php
 */
function takeRandom(string $subject): string {
    // on prend un index au hasard dans la chaine
    $index = random_int(0, strlen($subject) - 1);

    // en PHP, les chaines sont considérés implicitement comme des tableaux
    // on peut donc récupérer un char via son index comme suit
    $randomChar = $subject[$index];

    return $randomChar;
}

/**
 * Generates a random password based on the given parameters and ensures character type diversity.
 *
 * @param int $size The length of the password to be generated.
 * @param bool $useAlphaMin Whether to include lowercase alphabetic characters in the password.
 * @param bool $useAlphaMaj Whether to include uppercase alphabetic characters in the password.
 * @param bool $useNum Whether to include numerical characters in the password.
 * @param bool $useSymbols Whether to include special symbols in the password.
 * @return string The generated random password.
 * @see https://www.php.net/manual/fr/function.array-rand.php
 * @see https://www.php.net/manual/fr/function.str-shuffle.php
 */
function generatePassword(
    int $size,
    bool $useAlphaMin,
    bool $useAlphaMaj,
    bool $useNum,
    bool $useSymbols
): string {

    $lower = "abcdefghijklmnopqrstuvwxyz";
    $upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $numbers = "0123456789";
    $symbols = "!@#$%^&*()";


    $pool = "";

    if ($useAlphaMin) $pool .= $lower;
    if ($useAlphaMaj) $pool .= $upper;
    if ($useNum) $pool .= $numbers;
    if ($useSymbols) $pool .= $symbols;

    // Si l'utilisateur ne coche rien → erreur
    if ($pool === "") {
        return "Veuillez sélectionner au moins un type de caractères.";
    }

    $password = "";

    // Génération sécurisée du mot de passe
    for ($i = 0; $i < $size; $i++) {
        $password .= $pool[random_int(0, strlen($pool) - 1)];
    }

    return $password;
}


// -- params
$generated = "...";
$size = $_POST['size'] ?? 12;
$useAlphaMin = $_POST['use-alpha-min'] ?? 0;
$useAlphaMaj = $_POST['use-alpha-maj'] ?? 0;
$useNum = $_POST['use-num'] ?? 0;
$useSymbols = $_POST['use-symbols'] ?? 0;

// -- generate password

// $_SERVER est une autre variable mise à disposition par PHP automatiquement
// avec les informations qui viennent du serveur, de la requête HTTP, et d'autres informations
// 
// Ici, si la requête est POST -> le formulaire a été envoyé, donc on génère un mot de passe
// sinon, c'est que c'est la première fois qu'on visite la page, et donc, on affiche tout par défaut
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $generated = generatePassword($size, $useAlphaMin, $useAlphaMaj, $useNum, $useSymbols);
} else {
    // on surcharge les variables (on les mets à 1) pour forcer l'affichage
    // des cases à cocher comme "cochée"
    $useAlphaMin = 1;
    $useAlphaMaj = 1;
    $useNum = 1;
    $useSymbols = 1;
}

// -- render

// on génère les options du select pour la taille du mot de passe
$sizeSelectorOptions = generateSelectOptions($size);

// on voit si on doit pré-cocher les cases à cocher ou pas
$useAlphaMinChecked = $useAlphaMin == 1 ? "checked" : "";
$useAlphaMajChecked = $useAlphaMaj == 1 ? "checked" : "";
$useNumChecked = $useNum == 1 ? "checked" : "";
$useSymbolsChecked = $useSymbols == 1 ? "checked" : "pas-checked";

// on génère notre page
$page = <<<HTML
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Générateur de mot de passe</title>
    <style>
        body{
        background-color: deepskyblue; 
        text-align: center;
        }
        .container{
            padding: 10px;
            background-color: white;
            margin: 130px;
            margin-right: 400px;
            margin-left: 400px;
            font-family: Arial;
            border-radius: 20px;
        }
        h1{
        border-radius: 10px;
        border: solid 2px black;
        padding: 10px;
        margin: 40px;
        }
        .generate{
        border: solid 2px black;
        padding: 10px;
        border-radius: 10px;
        margin: 40px;
        }
    </style>
  </head>
  <body>

    <div class="container">
        <h1>Générateur de mot de passe</h1>

        <div >
            <div>
                <div role="alert">
                  <div class="generate">{$generated}</div>
                </div>
            </div>
        </div>
        
        <div>
            <div>
               <h4>Paramètres</h4>
            
                <form method="POST" action="/">
                    <div>
                        <label for="size">Taille</label>
                        <select aria-label="Default select example" name="size">
                            {$sizeSelectorOptions}
                        </select>
                    </div>
                    <div>
                      <input type="checkbox" value="1" id="use-alpha-min" name="use-alpha-min" {$useAlphaMinChecked}>
                      <label for="use-alpha-min">
                        Utiliser les lettres minuscules (a-z)
                      </label>
                    </div>
                    <div class="form-check">
                      <input type="checkbox" value="1" id="use-alpha-maj" name="use-alpha-maj" {$useAlphaMajChecked}>
                      <label for="use-alpha-maj">
                        Utiliser les lettres majuscules (A-Z)
                      </label>
                    </div>
                    <div>
                      <input type="checkbox" value="1" id="use-num" name="use-num" {$useNumChecked}>
                      <label for="use-num">
                        Utiliser les chiffres (0-9)
                      </label>
                    </div>
                    <div>
                      <input type="checkbox" value="1" id="use-symbols" name="use-symbols" {$useSymbolsChecked}>
                      <label for="use-symbols">
                        Utiliser les symboles (!@#$%^&*())
                      </label>
                    </div>
                    
                    <div>
                        <button type="submit">Générer !</button>
                    </div>
                </form>
            </div>

        </div>


    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
  </body>
</html>
HTML;

echo $page;
