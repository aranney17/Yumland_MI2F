<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: fichiers_php/connexion.php");
    exit();
}

/* SECURITE cuisinier + bloque */
$clients = json_decode(file_get_contents('data/infoclient.json'), true) ?? [];
$role = null;
$bloque = false;
foreach ($clients as $c) {
    if ($c['id'] == $_SESSION['id']) {
        $role = $c['role'];
        $bloque = $c['bloque'] ?? false;
        break;
    }
}
if ($bloque) {
    session_destroy();
    die("Votre compte a été bloqué. <a href='fichiers_php/connexion.php'>Retour</a>");
}
if ($role !== 'cuisinier') {
    http_response_code(403);
    die("Accès refusé. Cette page est réservée aux cuisiniers.");
}

$fichierCommandes = "data/commande.json";
$commandes = json_decode(file_get_contents($fichierCommandes), true) ?? [];

$modifie = false;
if (isset($_GET['commencer'])) {
    $ref = $_GET['commencer'];
    foreach ($commandes as &$cmd) {
        if ($cmd['reference'] === $ref && $cmd['statut'] === "a preparer") {
            $cmd['statut'] = "en preparation";
            $cmd['modifie_par_client'] = false;
            $modifie = true;
            break;
        }
    }
    unset($cmd);
}
if (isset($_GET['terminer'])) {
    $ref = $_GET['terminer'];
    foreach ($commandes as &$cmd) {
        if ($cmd['reference'] === $ref && $cmd['statut'] === "en preparation") {
            $cmd['statut'] = "commande préparée";
            $modifie = true;
            break;
        }
    }
    unset($cmd);
}
if ($modifie) {
    file_put_contents($fichierCommandes, json_encode($commandes, JSON_PRETTY_PRINT));
    header("Location: fichiers_php/commandes.php");
    exit();
}

$ajd = date("Y-m-d");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="fichiers_css/commandes.css">
    <link rel="stylesheet" href="fichiers_css/structg.css">
    <link rel="stylesheet" href="fichiers_css/couleurs.css">
    <link rel="stylesheet" href="fichiers_css/darkmode.css">
    <title>Commandes</title>
    <style>
        .commande-case.a-preparer     { border-left: 6px solid #f0a040; }
        .commande-case.en-preparation { border-left: 6px solid #4080f0; }
        .badge-statut { display:inline-block; padding:2px 8px; border-radius:10px; font-size:12px; color:white; margin-right:5px; }
        .badge-a-preparer     { background: #f0a040; }
        .badge-en-preparation { background: #4080f0; }
        .badge-modifiee       { background: #c03030; }
        .alerte-modif { background:#ffe8e8; border-left:4px solid #c03030; padding:6px 10px; margin:8px 0; font-size:13px; }
        body.dark .alerte-modif { background:#4a2020; }
    </style>
</head>
<body>

<header>
    <div class="barres"><span></span><span></span><span></span></div>
    <!-- LOGO : confine au cuisinier -->
    <h1><a href="fichiers_php/commandes.php" class="logo">La Cour des Délices</a></h1>
    <div class="top-icons">
        <div class="profil-menu">
            <img src="images/Iconprofil.png" class="icon">
            <div class="profil-bulle">
                <a href="fichiers_php/profil.php">Profil</a>
                <a href="fichiers_php/logout.php">Déconnexion</a>
            </div>
        </div>
    </div>
</header>

<main>
    <h2>Commandes du jour</h2>
    <div class="commandes">
    <?php foreach ($commandes as $cmd):
        $estAPreparer     = ($cmd['statut'] === "a preparer");
        $estEnPreparation = ($cmd['statut'] === "en preparation");
        $estModifiee      = !empty($cmd['modifie_par_client']);
        if (($estAPreparer || $estEnPreparation) && $cmd['datelivraison'] === $ajd):
            $classeCase = $estAPreparer ? "a-preparer" : "en-preparation";
    ?>
        <div class="commande-case <?= $classeCase ?>">
            <div class="commande-header">
                <span class="numero"><?= $cmd['reference'] ?></span>
                <span class="prix"><?= $cmd['montant'] ?>€</span>
            </div>
            <p>
                <?php if ($estAPreparer): ?>
                    <span class="badge-statut badge-a-preparer">À préparer</span>
                <?php else: ?>
                    <span class="badge-statut badge-en-preparation">En préparation</span>
                <?php endif; ?>
                <?php if ($estModifiee && $estAPreparer): ?>
                    <span class="badge-statut badge-modifiee">⚠ Modifiée</span>
                <?php endif; ?>
            </p>
            <?php if ($estModifiee && $estAPreparer): ?>
                <div class="alerte-modif">
                    Commande modifiée par le client
                    <?php if (!empty($cmd['date_revalidation'])): ?>
                        le <?= $cmd['date_revalidation'] ?>
                    <?php endif; ?>.
                </div>
            <?php endif; ?>
            <div class="commande-details">
                <?php foreach ($cmd['produits'] as $produit): ?>
                    <?= $produit['produit'] ?> x<?= $produit['quantite'] ?><br>
                <?php endforeach; ?>
            </div>
            <?php if ($estAPreparer): ?>
                <a href="fichiers_php/commandes.php?commencer=<?= $cmd['reference'] ?>" class="btn">Commencer la préparation</a>
            <?php else: ?>
                <a href="fichiers_php/commandes.php?terminer=<?= $cmd['reference'] ?>" class="btn">Terminer</a>
            <?php endif; ?>
        </div>
    <?php endif; endforeach; ?>
    </div>
</main>

<footer>
    <p>suivez nous sur nos réseaux!<br>
        <img src="images/Iconinstagram.jpg" class="icon">
        <img src="images/Icontiktok.jpg" class="icon">
        <img src="images/Icontwitter.png" class="icon">
    </p>
    <div class="infos-footer">
        <div class="info"><img src="images/Iconlocalisation.png" class="icon"><span>5 avenue de la république, 75015 Paris</span></div>
        <div class="info"><img src="images/Iconhorloge.png" class="icon"><span>Tous les jours 9h - 20h</span></div>
    </div>
    <h5>© 2026 Pâtisserie</h5>
</footer>

<button id="btn-darkmode" class="btn-darkmode">☾</button>
<script src="darkmode.js"></script>
</body>
</html>
