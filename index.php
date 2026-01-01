<?php
require_once 'Card.php';
session_start();

$pairs = 8;

/* =========================
   INITIALISATION / RESTART
========================= */
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
    $_SESSION['pending_hide'] = [];
    $_SESSION['moves'] = 0;

    // ✅ PRG: évite le scroll + resoumission
    if (isset($_POST['restart'])) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

/* =========================
   RÉCUPÉRATION DU DECK
========================= */
$deck = unserialize($_SESSION['deck']);

/* =========================
   CACHER MAUVAISE PAIRE
========================= */
if (!empty($_SESSION['pending_hide'])) {
    foreach ($_SESSION['pending_hide'] as $idx) {
        if (isset($deck[$idx]) && !$deck[$idx]->matched) {
            $deck[$idx]->flipped = false;
        }
    }
    $_SESSION['pending_hide'] = [];
}

/* =========================
   TRAITEMENT DU CLIC
========================= */
if (isset($_POST['cardId'])) {
    $cardId = (int) $_POST['cardId'];

    $clickedIndex = -1;
    foreach ($deck as $index => $card) {
        if ($card->getId() === $cardId) {
            $clickedIndex = $index;
            break;
        }
    }

    if ($clickedIndex !== -1 && !$deck[$clickedIndex]->matched && !$deck[$clickedIndex]->flipped) {

        if (count($_SESSION['flipped_cards']) === 1) {
            $_SESSION['moves']++;
        }

        $deck[$clickedIndex]->flipped = true;
        $_SESSION['flipped_cards'][] = $clickedIndex;

        if (count($_SESSION['flipped_cards']) === 2) {
            [$i1, $i2] = $_SESSION['flipped_cards'];

            if ($deck[$i1]->getImage() === $deck[$i2]->getImage()) {
                $deck[$i1]->matched = true;
                $deck[$i2]->matched = true;
            } else {
                $_SESSION['pending_hide'] = [$i1, $i2];
            }

            $_SESSION['flipped_cards'] = [];
        }

        $_SESSION['deck'] = serialize($deck);

        // ✅ PRG: évite le scroll automatique / “la carte en bas”
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

/* =========================
   VICTOIRE ?
========================= */
$allMatched = true;
foreach ($deck as $card) {
    if (!$card->matched) {
        $allMatched = false;
        break;
    }
}

$_SESSION['deck'] = serialize($deck);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Jeu de Mémoire</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- IMPORTANT : ce fichier doit exister à côté de index.php -->
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
            <p>Vous avez gagné en <strong><?= $_SESSION['moves'] ?></strong> coups.</p>

            <form method="post">
                <button type="submit" name="restart" class="btn-restart">🔄 Rejouer</button>
            </form>
        </div>
    <?php else: ?>
        <form method="post" class="game-board">
            <?php foreach ($deck as $card): ?>
                <button
                    type="submit"
                    name="cardId"
                    value="<?= $card->getId() ?>"
                    class="card <?= $card->matched ? 'matched' : '' ?>"
                    <?= ($card->matched || count($_SESSION['flipped_cards']) >= 2) ? 'disabled' : '' ?>
                >
                    <img
                        src="<?= ($card->flipped || $card->matched) ? $card->getImage() : "./assets/backside.png" ?>"
                        alt="Carte"
                    >
                </button>
            <?php endforeach; ?>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
