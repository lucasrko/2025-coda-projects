<?php
$player = $_GET['player'] ?? null;
$choices = ["Pierre", "Feuille", "Ciseaux"];
$scoreJoueur = 0;
$scoreStockfish= 0;
$StockfishChoice = null;
$resultat = "Bon courage &#x1F480;";
if ($player !== null) {

    $StockfishChoice = $choices[array_rand($choices)];

}
if ($player !== null) {
    if ($player === $StockfishChoice) {
        $resultat = "Égalité";
}
    elseif (
        ($player === "pierre" && $StockfishChoice === "ciseaux") ||
        ($player === "feuille" && $StockfishChoice === "pierre") ||
        ($player === "ciseaux" && $StockfishChoice === "feuille")
    )
    {
        $resultat = "Gagné !";
        $scoreJoueur = $scoreJoueur + 1 ;
    }
    else {
        $resultat = "Perdu !";
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
        body { text-align: center}
        
        button { padding: 10px; margin: 5px }
    </style>

</head>
<body>
    <h1> Jeu Pierre, Feuille, Ciseaux </h1>
    <div>
        <strong>Choix du joueur :</strong><br>
        {$player}
    </div>
    <div>
        <strong>Choix de Stockfish :</strong><br>
        {$StockfishChoice}
    </div>
    <div>
        <strong>Résultat :</strong><br>
        {$resultat}
    </div>
    <div>
        <a href="?player=pierre"><button>Pierre</button></a>
        <a href="?player=feuille"><button>Feuille</button></a>
        <a href="?player=ciseaux"><button>Ciseaux</button></a>
        <a href="/"><button>Réinitialiser</button></a> 
    </div>
    <div>Vous:$scoreJoueur</div>
    <div>Stockfish:$scoreStockfish</div>
    
</body>

</html>
HTML;

$html = str_replace("HTML_PLAYER", $player ?? "Faites votre choix...", $html);
$html = str_replace("HTML_PHP", $StockfishChoice ?? "...", $html);
$html = str_replace("HTML_RESULT", $resultat, $html);

echo $html;