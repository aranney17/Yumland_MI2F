<?php
session_start();

/*  SECURITE : reserve aux livreurs + check si bloque */
if (!isset($_SESSION['id'])) {
    header("Location: ../fichiers_php/connexion.php");
    exit();
}
$monId = $_SESSION['id'];

$clients = json_decode(file_get_contents('../data/infoclient.json'), true) ?? [];
$roleUser = null;
$userBloque = false;
foreach ($clients as $c) {
    if ($c['id'] == $monId) {
        $roleUser = $c['role'];
        $userBloque = $c['bloque'] ?? false;
        break;
    }
}

if ($userBloque) {
    session_destroy();
    die("Votre compte a été bloqué. <a href='../fichiers_php/connexion.php'>Retour à la connexion</a>");
}

if ($roleUser !== 'livreur') {
    http_response_code(403);
    die("Accès refusé. Cette page est réservée aux livreurs.");
}

/* Logique livraison */
$commandes = json_decode(file_get_contents('../data/commande.json'), true) ?? [];

/* DEMARRER : on reserve la livraison a ce si la commande est encore "commande préparée" */
if (isset($_POST['start_id'])) {
    foreach ($commandes as &$cmd) {
        if ($cmd['id'] == $_POST['start_id'] && $cmd['statut'] === 'commande préparée') {
            $cmd['statut'] = 'en_livraison';
            $cmd['livreur_id'] = $monId;          // livreur qui l'a prise
        }
    }
    unset($cmd);
    file_put_contents('../data/commande.json', json_encode($commandes, JSON_PRETTY_PRINT));
    header('Location: ../fichiers_php/livraison.php');
    exit;
}

/* TERMINER : seul le livreur qui a pris la livraison peut la terminer. */
if (isset($_POST['finish_id'])) {
    foreach ($commandes as &$cmd) {
        if ($cmd['id'] == $_POST['finish_id'] && ($cmd['livreur_id'] ?? null) == $monId) {
            $cmd['statut'] = 'terminee';
            $cmd['notif_vue'] = false;
        }
    }
    unset($cmd);
    file_put_contents('../data/commande.json', json_encode($commandes, JSON_PRETTY_PRINT));
    header('Location: ../fichiers_php/livraison.php');
    exit;
}

/* livraison en cours */
$commande_en_cours = null;
foreach ($commandes as $cmd) {
    if ($cmd['statut'] === 'en_livraison' && ($cmd['livreur_id'] ?? null) == $monId) {
        $commande_en_cours = $cmd;
        break;
    }
}

/* Commandes pretes a livrer : livraison classique et traiteur.*/
$commandes_filtrees = array_filter($commandes, function($cmd) {
    return in_array($cmd['type_commande'] ?? '', ['livraison', 'traiteur'], true)
        && $cmd['statut'] === 'commande préparée';
});
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livraison</title>
    <link rel="stylesheet" href="../fichiers_css/couleurs.css">
    <link rel="stylesheet" href="../fichiers_css/structg.css">
    <link rel="stylesheet" href="../fichiers_css/livraison.css">
    <link rel="stylesheet" href="../fichiers_css/darkmode.css">
    
</head>
<body>

<h1><a href="../fichiers_php/livraison.php" class="logo">La Cour des Délices</a></h1>

<div class="top-icons">
    <div class="profil-menu">
        <img src="../images/Iconprofil.png" class="icon">
        <div class="profil-bulle">
            <a href="../fichiers_php/profil.php">Profil</a>
            <a href="../fichiers_php/logout.php">Déconnexion</a>
        </div>
    </div>
</div>

<?php if ($commande_en_cours): ?>
<div class="container">
    <div class="commande-card">
        <?php if (($commande_en_cours['type_commande'] ?? '') === 'traiteur'): ?>
            <span class="badge-traiteur">🎂 Traiteur — événement le <?= htmlspecialchars($commande_en_cours['evenement']['date'] ?? $commande_en_cours['datelivraison']) ?></span>
        <?php endif; ?>
        <h2>Client : <?= htmlspecialchars($commande_en_cours['prenom'] . " " . $commande_en_cours['nom']) ?></h2>
        <p class="ref">Commande #<?= htmlspecialchars($commande_en_cours['reference']) ?></p>
        <a class="adresse"
           href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($commande_en_cours['adresse']) ?>"
           target="_blank">
            <img src="../images/Iconlocalisation.png" class="map-icon">
            <span><?= htmlspecialchars($commande_en_cours['adresse']) ?></span>
        </a>
        <div class="infos">
            <p><strong>Téléphone :</strong> <?= htmlspecialchars($commande_en_cours['telephone']) ?></p>
        </div>
        <form method="POST">
            <input type="hidden" name="finish_id" value="<?= htmlspecialchars($commande_en_cours['id']) ?>">
            <button class="finish-btn">Terminer la livraison</button>
        </form>
    </div>
</div>
<?php else: ?>
<div class="container">
<?php foreach ($commandes_filtrees as $cmd): ?>
    <div class="commande-card">
        <?php if (($cmd['type_commande'] ?? '') === 'traiteur'): ?>
            <span class="badge-traiteur">🎂 Traiteur — événement le <?= htmlspecialchars($cmd['evenement']['date'] ?? $cmd['datelivraison']) ?></span>
        <?php endif; ?>
        <h2>Client : <?= htmlspecialchars($cmd['prenom'] . " " . $cmd['nom']) ?></h2>
        <p class="ref">Commande #<?= htmlspecialchars($cmd['reference']) ?></p>
        <a class="adresse"
           href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($cmd['adresse']) ?>"
           target="_blank">
            <img src="../images/Iconlocalisation.png" class="map-icon">
            <span><?= htmlspecialchars($cmd['adresse']) ?></span>
        </a>
        <div class="infos">
            <p><strong>Téléphone :</strong> <?= htmlspecialchars($cmd['telephone']) ?></p>
        </div>
        <form method="POST">
            <input type="hidden" name="start_id" value="<?= htmlspecialchars($cmd['id']) ?>">
            <button class="finish-btn">Démarrer la livraison</button>
        </form>
    </div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<footer>
<p>suivez nous sur nos réseaux!<br>
    <img src="../images/Iconinstagram.jpg" class="icon">
    <img src="../images/Icontiktok.jpg" class="icon">
    <img src="../images/Icontwitter.png" class="icon">
</p>
<div class="infos-footer">
    <div class="info"><img src="../images/Iconlocalisation.png" class="icon"><span>5 avenue de la république, 75015 Paris</span></div>
    <div class="info"><img src="../images/Iconhorloge.png" class="icon"><span>Tous les jours 9h - 20h</span></div>
</div>
<h5>© 2026 Pâtisserie</h5>
</footer>

<button id="btn-darkmode" class="btn-darkmode">☾</button>
<script src="../fichiers_js/darkmode.js"></script>
</body>
</html>
