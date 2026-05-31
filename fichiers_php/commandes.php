<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../fichiers_php/connexion.php");
    exit();
}
$monId = $_SESSION['id'];

/* SECURITE cuisinier + bloque */
$clients = json_decode(file_get_contents('../data/infoclient.json'), true) ?? [];
$role = null;
$bloque = false;
foreach ($clients as $c) {
    if ($c['id'] == $monId) {
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

/* COMMENCER : on reserve la commande a CE cuisinier.
   Possible seulement si elle est encore "a preparer" (personne ne l'a prise). */
if (isset($_GET['commencer'])) {
    $ref = $_GET['commencer'];
    foreach ($commandes as &$cmd) {
        if ($cmd['reference'] === $ref && $cmd['statut'] === "a preparer") {
            $cmd['statut'] = "en preparation";
            $cmd['cuisinier_id'] = $monId;          // qui l'a prise
            $cmd['modifie_par_client'] = false;
            $modifie = true;
            break;
        }
    }
    unset($cmd);
}

/* TERMINER : seul le cuisinier qui a pris la commande peut la terminer. */
if (isset($_GET['terminer'])) {
    $ref = $_GET['terminer'];
    foreach ($commandes as &$cmd) {
        if ($cmd['reference'] === $ref
            && $cmd['statut'] === "en preparation"
            && ($cmd['cuisinier_id'] ?? null) == $monId) {
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

/* -------------------------------------------------------------
   Qui voit quoi :
   - "a preparer"    : visible par TOUS les cuisiniers, selon le
     filtre de date (jour meme, ou <= 5 jours pour un traiteur).
   - "en preparation": visible UNIQUEMENT par le cuisinier qui l'a
     prise (cuisinier_id), sans filtre de date (il la garde jusqu'a
     l'avoir terminee).
------------------------------------------------------------- */
function aAfficher($cmd, $ajd, $monId) {
    $statut = $cmd['statut'];

    if ($statut === 'en preparation') {
        $proprio = $cmd['cuisinier_id'] ?? null;
        if ($proprio === null) return true;   // ancienne commande sans proprietaire (compat)
        return ($proprio == $monId);
    }

    if ($statut !== 'a preparer') return false;

    // statut "a preparer" -> filtre de date
    if (($cmd['type_commande'] ?? '') === 'traiteur') {
        $joursRestants = (strtotime($cmd['datelivraison']) - strtotime($ajd)) / 86400;
        return ($joursRestants >= 0 && $joursRestants <= 5);
    }
    return ($cmd['datelivraison'] === $ajd);
}
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
    <style>
        .bloc-traiteur {
            background: var(--orange-fond);
            color: var(--orange-texte);
            border-left: 4px solid var(--orange-texte);
            border-radius: 6px;
            padding: 10px 12px;
            margin: 8px 0;
            font-size: 0.92em;
        }
        .bloc-traiteur ul { margin: 6px 0 0 16px; }
    </style>
</head>
<body>

<header>
    
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
    <h2>Commandes à préparer</h2>
    <div class="commandes">
    <?php foreach ($commandes as $cmd):
        if (!aAfficher($cmd, $ajd, $monId)) continue;

        $estAPreparer     = ($cmd['statut'] === "a preparer");
        $estEnPreparation = ($cmd['statut'] === "en preparation");
        $estModifiee      = !empty($cmd['modifie_par_client']);
        $estTraiteur      = (($cmd['type_commande'] ?? '') === 'traiteur');
        $classeCase       = $estAPreparer ? "a-preparer" : "en-preparation";
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
                <?php if ($estTraiteur): ?>
                    <span class="badge-statut badge-modifiee">🎂 Traiteur</span>
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

            <?php if ($estTraiteur): ?>
                <div class="bloc-traiteur">
                    <strong>Événement le <?= htmlspecialchars($cmd['evenement']['date'] ?? $cmd['datelivraison']) ?></strong>
                    à <?= htmlspecialchars($cmd['evenement']['lieu'] ?? $cmd['adresse']) ?><br>
                    <?php if (!empty($cmd['piece_montee'])): ?>
                        Pièce montée : <?= htmlspecialchars($cmd['piece_montee']['etages']) ?> étage(s)
                        <ul>
                            <li>Glaçage : <?= htmlspecialchars($cmd['piece_montee']['glacage']) ?></li>
                            <li>Génoise : <?= htmlspecialchars($cmd['piece_montee']['genoise']) ?></li>
                            <li>Garniture : <?= htmlspecialchars($cmd['piece_montee']['garniture']) ?></li>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="commande-details">
                <?php
                if ($estTraiteur) echo "<strong>Accompagnements :</strong><br>";
                foreach ($cmd['produits'] as $produit):
                    $estMenu        = (($produit['type'] ?? '') === 'menu');
                    $estPieceMontee = (($produit['type'] ?? '') === 'piece_montee');
                    if ($estPieceMontee) continue;   // deja affichee dans le bloc traiteur
                ?>
                    <?php if ($estMenu): ?>
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
    <?php endforeach; ?>
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
