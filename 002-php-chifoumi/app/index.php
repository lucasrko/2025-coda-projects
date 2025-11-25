<?php

$player = $_GET['player'] ?? null;
$choices = ["pierre", "feuille", "ciseaux"];
$scoreJoueur = 0;
$scoreStockfish= 0;

$phpChoice = null;
if ($player !== null) {
    $phpChoice = $choices[array_rand($choices)];
}
$result = "Faire un choix pour commencer";

if ($player !== null) {
    if ($player === $phpChoice) {
        $result = "Égalité";
    } elseif (
        ($player === "pierre" && $phpChoice === "ciseaux") ||
        ($player === "feuille" && $phpChoice === "pierre") ||
        ($player === "ciseaux" && $phpChoice === "feuille")
    ) {
        $result = "Gagné";
        $scoreJoueur = $scoreJoueur + 1 ;
    } else {
        $result = "Perdu";
        $scoreStockfish = $scoreStockfish + 1 ;
    }
}

$html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Jeu Pierre, Feuille, Ciseaux</title>
    <style>
        body { text-align: center; margin-top: 40px; }
        .zone { margin: 20px; font-size: 20px; }
        button { padding: 10px 20px; margin: 5px; font-size: 16px; border-radius: 10px; }
    </style>
</head>
<body>

    <h1>Jeu Pierre, Feuille, Ciseaux</h1>

    <div class="zone">
        <strong>Choix du joueur :</strong><br>
        {$player}
    </div>

    <div class="zone">
        <strong>Choix de Stockfish :</strong><br>
        {$phpChoice}
    </div>

    <div class="zone">
        <strong>Résultat :</strong><br>
        {$result}
    </div>

    <div class="zone">
        <a href="?player=pierre"><button>Pierre</button></a>
        <a href="?player=feuille"><button>Feuille</button></a>
        <a href="?player=ciseaux"><button>Ciseaux</button></a>
        <a href="/"><button>Réinitialiser</button></a>
    </div>
    <div class="zone">Vous:$scoreJoueur</div>
    <div class="zone">Stockfish:$scoreStockfish</div>

</body>
</html>
HTML;

$html = str_replace("HTML_PLAYER", $player ?? "En attente d'un choix...", $html);
$html = str_replace("HTML_PHP", $phpChoice ?? "En attente...", $html);
$html = str_replace("HTML_RESULT", $result, $html);

echo $html;

?>