<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../fichiers_php/connexion.php");
    exit();
}

/* SECURITE cuisinier + bloque */
$clients = json_decode(file_get_contents('../data/infoclient.json'), true) ?? [];
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
    die("Votre compte a été bloqué. <a href='../fichiers_php/connexion.php'>Retour</a>");
}
if ($role !== 'cuisinier') {
    http_response_code(403);
    die("Accès refusé. Cette page est réservée aux cuisiniers.");
}

$fichierCommandes = "../data/commande.json";
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
    header("Location: ../fichiers_php/commandes.php");
    exit();
}

$ajd = date("Y-m-d");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../fichiers_css/commandes.css">
    <link rel="stylesheet" href="../fichiers_css/structg.css">
    <link rel="stylesheet" href="../fichiers_css/couleurs.css">
    <link rel="stylesheet" href="../fichiers_css/darkmode.css">
    <title>Commandes</title>
</head>
<body>

<header>
    <div class="barres"><span></span><span></span><span></span></div>
    <h1><a href="../fichiers_php/commandes.php" class="logo">La Cour des Délices</a></h1>
    <div class="top-icons">
        <div class="profil-menu">
            <img src="../images/Iconprofil.png" class="icon">
            <div class="profil-bulle">
                <a href="../fichiers_php/profil.php">Profil</a>
                <a href="../fichiers_php/logout.php">Déconnexion</a>
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
                <span class="numero"><?= htmlspecialchars($cmd['reference']) ?></span>
                <span class="prix"><?= htmlspecialchars($cmd['montant']) ?>€</span>
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
                        le <?= htmlspecialchars($cmd['date_revalidation']) ?>
                    <?php endif; ?>.
                </div>
            <?php endif; ?>

            <div class="commande-details">
                <?php foreach ($cmd['produits'] as $produit):
                    $estMenu = (isset($produit['type']) && $produit['type'] === 'menu');
                ?>
                    <?php if ($estMenu): ?>
                        <!-- MENU : on montre le detail au cuisinier -->
                        <div class="menu-item">
                            <span class="titre-menu">🍽 <?= htmlspecialchars($produit['nom_menu']) ?></span>
                            x<?= htmlspecialchars($produit['quantite']) ?>
                            <ul>
                                <?php foreach ($produit['composition'] as $label => $choix): ?>
                                    <li><?= htmlspecialchars($label) ?> : <?= htmlspecialchars($choix) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <?= htmlspecialchars($produit['produit']) ?> x<?= htmlspecialchars($produit['quantite']) ?><br>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <?php if ($estAPreparer): ?>
                <a href="../fichiers_php/commandes.php?commencer=<?= urlencode($cmd['reference']) ?>" class="btn">Commencer la préparation</a>
            <?php else: ?>
                <a href="../fichiers_php/commandes.php?terminer=<?= urlencode($cmd['reference']) ?>" class="btn">Terminer</a>
            <?php endif; ?>
        </div>
    <?php endif; endforeach; ?>
    </div>
</main>

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
