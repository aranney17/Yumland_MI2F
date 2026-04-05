<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Charger JSON
$commandes = json_decode(file_get_contents('data/commande.json'), true);
if (!$commandes) $commandes = [];

//démarrer
if (isset($_POST['start_id'])) {
    foreach ($commandes as &$cmd) {
        if ($cmd['id'] == $_POST['start_id']) {
            $cmd['statut'] = 'en_livraison';
        }
    }
    file_put_contents('data/commande.json', json_encode($commandes, JSON_PRETTY_PRINT));
    header('Location: livraison.php');
    exit;
}

// terminer
if (isset($_POST['finish_id'])) {
    foreach ($commandes as &$cmd) {
        if ($cmd['id'] == $_POST['finish_id']) {
            $cmd['statut'] = 'terminee';
        }
    }
    file_put_contents('data/commande.json', json_encode($commandes, JSON_PRETTY_PRINT));
    header('Location: livraison.php');
    exit;
}

// commande en cours
$commande_en_cours = null;
foreach ($commandes as $cmd) {
    if ($cmd['statut'] === 'en_livraison') {
        $commande_en_cours = $cmd;
        break;
    }
}

// commandes disponibles
$commandes_filtrees = array_filter($commandes, function($cmd) {
    return $cmd['type_commande'] === 'livraison' && $cmd['statut'] === 'commande préparée';
});
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livraison</title>

    <link rel="stylesheet" href="couleurs.css">
    <link rel="stylesheet" href="structg.css">
    <link rel="stylesheet" href="livraison.css">
</head>

<body>

<div class="barres">
    <span></span><span></span><span></span>
</div>

<h1><a href="accueil.php" class="logo">La Cour des Délices</a></h1>

<div class="top-icons">
    <div class="profil-menu">
        <img src="images/Iconprofil.png" class="icon">
        <div class="profil-bulle">
            <a href="profil.php">Profil</a>
            <a href="accueil.php">Déconnexion</a>
        </div>
    </div>
</div>

<?php if ($commande_en_cours): ?>

<!-- COMMANDE EN COURS -->
<div class="container">

    <div class="commande-card">

        <h2>Client : <?= $commande_en_cours['prenom'] . " " . $commande_en_cours['nom'] ?></h2>
        <p class="ref">Commande #<?= $commande_en_cours['reference'] ?></p>

        <a class="adresse"
           href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($commande_en_cours['adresse']) ?>"
           target="_blank">

            <img src="images/Iconlocalisation.png" class="map-icon">
            <span><?= $commande_en_cours['adresse'] ?></span>
        </a>

        <div class="infos">
            <p><strong>Téléphone :</strong> <?= $commande_en_cours['telephone'] ?></p>
        </div>

        <form method="POST">
            <input type="hidden" name="finish_id" value="<?= $commande_en_cours['id'] ?>">
            <button class="finish-btn">Terminer la livraison</button>
        </form>

    </div>

</div>

<?php else: ?>

<!-- LISTE DES COMMANDES -->
<div class="container">

<?php foreach ($commandes_filtrees as $cmd): ?>

    <div class="commande-card">

        <h2>Client : <?= $cmd['prenom'] . " " . $cmd['nom'] ?></h2>
        <p class="ref">Commande #<?= $cmd['reference'] ?></p>

        <a class="adresse"
           href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($cmd['adresse']) ?>"
           target="_blank">

            <img src="images/Iconlocalisation.png" class="map-icon">
            <span><?= $cmd['adresse'] ?></span>
        </a>

        <div class="infos">
            <p><strong>Téléphone :</strong> <?= $cmd['telephone'] ?></p>
        </div>

        <form method="POST">
            <input type="hidden" name="start_id" value="<?= $cmd['id'] ?>">
            <button class="finish-btn" >Démarrer la livraison</button>
        </form>

    </div>

<?php endforeach; ?>

</div>

<?php endif; ?>

<footer>
<p>suivez nous sur nos réseaux!<br>
    <img src="images/Iconinstagram.jpg" class="icon">
    <img src="images/Icontiktok.jpg" class="icon">
    <img src="images/Icontwitter.png" class="icon">
</p>

<div class="infos-footer">
    <div class="info">
        <img src="images/Iconlocalisation.png" class="icon">
        <span>5 avenue de la république, 75015 Paris</span>
    </div>

    <div class="info">
        <img src="images/Iconhorloge.png" class="icon">
        <span>Tous les jours 9h - 20h</span>
    </div>
</div>

<h5>© 2026 Pâtisserie</h5>
</footer>

</body>
</html>
