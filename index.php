<?php
require_once 'Card.php';
session_start();

$pairs = 8;

// Initialiser le deck
if (!isset($_SESSION['deck']) || isset($_POST['restart'])) {
    $deck = [];

    for ($i = 1; $i <= $pairs; $i++) {
        $imagePath = "./assets/card" . $i . ".png";
        $deck[] = new Card($i * 2 - 1, $imagePath);
        $deck[] = new Card($i * 2, $imagePath);
    }

    shuffle($deck);
    $_SESSION['deck'] = serialize($deck);
    $_SESSION['flipped_cards'] = [];
    $_SESSION['pending_hide'] = []; // ✅ NEW
    $_SESSION['moves'] = 0;
}

// Désérialiser le deck
if (isset($_SESSION['deck']) && is_string($_SESSION['deck'])) {
    $deck = unserialize($_SESSION['deck']);
} else {
    $deck = [];
    for ($i = 1; $i <= $pairs; $i++) {
        $imagePath = "./assets/card" . $i . ".png";
        $deck[] = new Card($i * 2 - 1, $imagePath);
        $deck[] = new Card($i * 2, $imagePath);
    }
    shuffle($deck);
    $_SESSION['deck'] = serialize($deck);
    $_SESSION['flipped_cards'] = [];
    $_SESSION['pending_hide'] = [];
    $_SESSION['moves'] = 0;
}

// ✅ Cacher la mauvaise paire du tour précédent (au prochain clic)
if (!empty($_SESSION['pending_hide'])) {
    foreach ($_SESSION['pending_hide'] as $idx) {
        if (isset($deck[$idx]) && !$deck[$idx]->matched) {
            $deck[$idx]->flipped = false;
        }
    }
    $_SESSION['pending_hide'] = [];
    $_SESSION['deck'] = serialize($deck);
}

// Traiter le clic sur une carte
if (isset($_POST['cardId'])) {
    $cardId = intval($_POST['cardId']);

    // Trouver la carte cliquée
    $clickedIndex = -1;
    foreach ($deck as $index => $card) {
        if ($card->getId() == $cardId) {
            $clickedIndex = $index;
            break;
        }
    }

    // ✅ éviter matched + éviter re-cliquer une carte déjà retournée
    if ($clickedIndex !== -1 && !$deck[$clickedIndex]->matched && !$deck[$clickedIndex]->flipped) {

        // ✅ Compter un coup seulement quand on retourne la 2e carte
        if (count($_SESSION['flipped_cards']) == 1) {
            $_SESSION['moves']++;
        }

        $deck[$clickedIndex]->flipped = true;
        $_SESSION['flipped_cards'][] = $clickedIndex;

        // Si deux cartes sont retournées
        if (count($_SESSION['flipped_cards']) == 2) {
            $index1 = $_SESSION['flipped_cards'][0];
            $index2 = $_SESSION['flipped_cards'][1];

            // Vérifier si c'est une paire
            if ($deck[$index1]->getImage() === $deck[$index2]->getImage()) {
                $deck[$index1]->matched = true;
                $deck[$index2]->matched = true;
            } else {
                // ❗ Pas une paire : on laisse visibles et on cachera au prochain clic
                $_SESSION['pending_hide'] = [$index1, $index2];
            }

            $_SESSION['flipped_cards'] = [];
        }

        $_SESSION['deck'] = serialize($deck);
    }
}

// Vérifier si toutes les paires sont trouvées
$allMatched = true;
foreach ($deck as $card) {
    if (!$card->matched) {
        $allMatched = false;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memory Game - Jeu de Mémoire</title>
    <link rel="stylesheet" href="./style.css">
</head>
<body>
    <div class="container">
        <div class="stats">
            <h1>🎮 Jeu de Mémoire</h1>
            <p><strong>Coups :</strong> <?= $_SESSION['moves'] ?></p>
        </div>

        <?php if ($allMatched): ?>
            <div class="victory">
                <h2>🎉 Félicitations !</h2>
                <p class="victory-text">Vous avez gagné en <strong><?= $_SESSION['moves'] ?></strong> coups !</p>
           <form method="post" class="game-board">
    <?php foreach ($deck as $card): ?>
        <button ...>
            <div class="card ...">
                <img ...>
            </div>
        </button>
    <?php endforeach; ?>
</form>

           
              
        <?php endif; ?>
    </div>
</body>
</html>
