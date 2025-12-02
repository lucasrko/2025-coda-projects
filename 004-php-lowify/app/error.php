<?php

require_once 'inc/page.inc.php';

// Récupération du message d’erreur passé avec un GET
$errorMessage = $_GET["message"] ?? "Une erreur est survenue.";

$errorMessage = htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8');

// Génération du contenu HTML
$html = <<<HTML
    <div style="padding:20px;">
        <h1 style="color:red;">Erreur</h1>
        <p>$errorMessage</p>
        <a href="index.php" style="color:#1DB954; font-weight:bold;">
            ➜ Retour à l'accueil
        </a>
    </div>
HTML;

// Affichage de la page
echo (new HTMLPage(title: "Erreur - Lowify"))
    ->setupNavigationTransition()
    ->addContent($html)
    ->render();
