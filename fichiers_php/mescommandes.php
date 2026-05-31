<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../fichiers_php/connexion.php");
    exit();
}

$userId = $_SESSION['id'];

/* Charger client et toutes les commandes */
$fichierClients   = '../data/infoclient.json';
$fichierCo<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../fichiers_php/connexion.php");
    exit();
}

$userId = $_SESSION['id'];

/*  Charger client et toutes les commandes */
$fichierClients   = '../data/infoclient.json';
$fichierCommandes = '../data/commande.json';
$fichierProduits  = '../data/produits.json';

$clients   = json_decode(file_get_contents($fichierClients), true) ?? [];
$commandes = json_decode(file_get_contents($fichierCommandes), true) ?? [];
$produits  = json_decode(file_get_contents($fichierProduits), true) ?? [];

$clientCo = null;
foreach ($clients as $c) {
    if ($c['id'] == $userId) { $clientCo = $c; break; }
}
if (!$clientCo) { die("Client introuvable."); }

/*  verifier qu'une commande appartient bien au client connecte et statut "a preparer" */
function commandeModifiableParClient(&$commande, $clientCo) {
    return $commande['nom']       === $clientCo['nom']
        && $commande['prenom']    === $clientCo['prenom']
        && $commande['telephone'] === $clientCo['telephone']
        && $commande['statut']    === "a preparer";
}

// recalculer le montant d'une commande
function recalculerMontant(&$commande) {
    $total = 0;
    foreach ($commande['produits'] as $p) {
        $total += floatval($p['prix']) * intval($p['quantite']);
    }
    $commande['montant'] = $total;
}

/*  modif_quantite + supprimer_produit + ajouter_produit */
$messageInfo = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $refCommande = $_POST['ref'] ?? '';
    $action      = $_POST['action'] ?? '';

    foreach ($commandes as &$cmd) {
        if ($cmd['reference'] !== $refCommande) continue;
        if (!commandeModifiableParClient($cmd, $clientCo)) {
            $messageInfo = "Cette commande ne peut plus être modifiée.";
            break;
        }

        if ($action === 'modif_quantite') {
            $idx = (int) $_POST['index'];
            $qte = max(1, (int) $_POST['quantite']);
            if (isset($cmd['produits'][$idx])) {
                $cmd['produits'][$idx]['quantite'] = (string) $qte;
                recalculerMontant($cmd);
            }
        }
        elseif ($action === 'supprimer_produit') {
            $idx = (int) $_POST['index'];
            if (isset($cmd['produits'][$idx])) {
                array_splice($cmd['produits'], $idx, 1);
                if (count($cmd['produits']) === 0) {
                    $messageInfo = "Votre commande est désormais vide.";
                }
                recalculerMontant($cmd);
            }
        }
        elseif ($action === 'ajouter_produit') {
            $nomProduit = $_POST['nom_produit'] ?? '';
            $saveur     = $_POST['saveur'] ?? '';
            $qte        = max(1, (int) $_POST['quantite']);

            // Retrouver le produit dans produits.json
            $prodTrouve = null;
            foreach ($produits as $p) {
                if ($p['nom'] === $nomProduit) { $prodTrouve = $p; break; }
            }

            if ($prodTrouve) {
                $cmd['produits'][] = [
                    "produit"  => $prodTrouve['titre'],
                    "prix"     => (string) $prodTrouve['prix'],
                    "saveur"   => $saveur ?: ($prodTrouve['saveurs'][0] ?? ''),
                    "quantite" => (string) $qte
                ];
                recalculerMontant($cmd);
            }
        }
        break;
    }
    unset($cmd);

    file_put_contents($fichierCommandes, json_encode($commandes, JSON_PRETTY_PRINT));

    // Redirection pour eviter le re-submit au refresh
    header("Location: ../fichiers_php/mescommandes.php");
    exit();
}

/* Recuperer les commandes du client connecte pour l'affichage */
$mesCommandes = [];
foreach ($commandes as $cmd) {
    if ($cmd['nom']       === $clientCo['nom']
     && $cmd['prenom']    === $clientCo['prenom']
     && $cmd['telephone'] === $clientCo['telephone']) {
        $mesCommandes[] = $cmd;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes commandes</title>
    <link rel="stylesheet" href="../fichiers_css/couleurs.css">
    <link rel="stylesheet" href="../fichiers_css/structg.css">
    <link rel="stylesheet" href="../fichiers_css/darkmode.css">
    
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

    <nav class="sidebar" id="sidebar">
        <a href="#nouveautes">Nouveautés</a>
        <a href="../fichiers_php/presentation.php#viennoiseries">Viennoiseries</a>
        <a href="../fichiers_php/presentation.php#boissons">Boissons</a>
        <a href="../fichiers_php/presentation.php#gourmandises">Gourmandises</a>
        <a href="../fichiers_php/presentation.php#patisseries">Pâtisseries</a>
        <a href="../fichiers_php/presentation.php#gateaux">Gâteaux</a>
        <a href="../fichiers_php/presentation.php#tartes">Tartes</a>
        <a href="../fichiers_php/traiteur.php">Commande traiteur</a>
    </nav>
    <div class="barres"><span></span><span></span><span></span></div>
<h1><a href="../fichiers_php/accueil.php" class="logo">La Cour des Délices</a></h1>

<div class="top-icons">
    <div class="profil-menu">
        <img src="../images/Iconprofil.png" alt="Profil" class="icon">
        <div class="profil-bulle">
            <a href="../fichiers_php/profil.php">Profil</a>
            <a href="../fichiers_php/logout.php">Déconnexion</a>
        </div>
    </div>
    <a href="../fichiers_php/panier.php">
        <img src="../images/Iconpanier.png" alt="Panier" class="icon" id="panier">
    </a>
</div>

<div class="mes-commandes">

    <h2>Mes commandes</h2>

    <?php if ($messageInfo): ?>
        <div class="info-msg"><?= htmlspecialchars($messageInfo) ?></div>
    <?php endif; ?>

    <?php if (count($mesCommandes) === 0): ?>
        <p>Vous n'avez aucune commande.</p>
    <?php endif; ?>

    <?php foreach ($mesCommandes as $cmd):
        // Determiner classe et libelle du statut
        $statutClasse = "statut-a-preparer";
        $statutLabel  = "À préparer";
        if      ($cmd['statut'] === "en preparation")    { $statutClasse = "statut-en-prep";   $statutLabel = "En préparation"; }
        elseif  ($cmd['statut'] === "commande préparée") { $statutClasse = "statut-prepare";   $statutLabel = "Préparée"; }
        elseif  ($cmd['statut'] === "en_livraison")      { $statutClasse = "statut-livraison"; $statutLabel = "En livraison"; }
        elseif  ($cmd['statut'] === "terminee")          { $statutClasse = "statut-terminee";  $statutLabel = "Livrée"; }

        $modifiable = ($cmd['statut'] === "a preparer");
    ?>
        <div class="ma-commande">
            <h3>
                Commande #<?= htmlspecialchars($cmd['reference']) ?>
                <span class="statut <?= $statutClasse ?>"><?= $statutLabel ?></span>
            </h3>
            <p>
                <strong>Date livraison :</strong> <?= htmlspecialchars($cmd['datelivraison']) ?> &nbsp;|&nbsp;
                <strong>Type :</strong> <?= htmlspecialchars($cmd['type_commande']) ?> &nbsp;|&nbsp;
                <strong>Montant :</strong> <?= number_format($cmd['montant'], 2, ',', ' ') ?> €
            </p>

            <?php if (!$modifiable): ?>
                <p><em>Cette commande est en cours de traitement, vous ne pouvez plus la modifier.</em></p>
            <?php endif; ?>

            <div class="produits-commande">
                <?php foreach ($cmd['produits'] as $idx => $prod): ?>
                    <div class="produit-ligne">
                        <span class="nom">
                            <?= htmlspecialchars($prod['produit']) ?>
                            <?php if (!empty($prod['saveur'])): ?>
                                <small>(<?= htmlspecialchars($prod['saveur']) ?>)</small>
                            <?php endif; ?>
                        </span>
                        <span><?= number_format(floatval($prod['prix']), 2, ',', ' ') ?> €</span>

                        <?php if ($modifiable): ?>
                            <!-- Form de modif quantite -->
                            <form method="POST" class="form-inline">
                                <input type="hidden" name="ref" value="<?= htmlspecialchars($cmd['reference']) ?>">
                                <input type="hidden" name="action" value="modif_quantite">
                                <input type="hidden" name="index" value="<?= $idx ?>">
                                <input type="number" name="quantite" value="<?= $prod['quantite'] ?>" min="1" max="20" onchange="this.form.submit()">
                            </form>
                            <!-- Form suppression -->
                            <form method="POST" class="form-inline" onsubmit="return confirm('Retirer ce produit ?');">
                                <input type="hidden" name="ref" value="<?= htmlspecialchars($cmd['reference']) ?>">
                                <input type="hidden" name="action" value="supprimer_produit">
                                <input type="hidden" name="index" value="<?= $idx ?>">
                                <button type="submit">Retirer</button>
                            </form>
                        <?php else: ?>
                            <span>x<?= $prod['quantite'] ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($modifiable): ?>
                <!-- Formulaire d'ajout de produit -->
                <div class="ajout-produit">
                    <strong>Ajouter un produit à cette commande :</strong><br><br>
                    <form method="POST">
                        <input type="hidden" name="ref" value="<?= htmlspecialchars($cmd['reference']) ?>">
                        <input type="hidden" name="action" value="ajouter_produit">

                        <select name="nom_produit" required>
                            <option value="">-- choisir --</option>
                            <?php foreach ($produits as $p): ?>
                                <option value="<?= htmlspecialchars($p['nom']) ?>">
                                    <?= htmlspecialchars($p['titre']) ?> (<?= number_format($p['prix'], 2, ',', ' ') ?> €)
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <input type="text" name="saveur" placeholder="Saveur (optionnel)">
                        <input type="number" name="quantite" value="1" min="1" max="20">
                        <button type="submit">Ajouter</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

</div>

<footer>
    <p>suivez nous sur nos réseaux!<br>
        <img src="../images/Iconinstagram.jpg" class="icon">
        <img src="../images/Icontiktok.jpg" class="icon">
        <img src="../images/Icontwitter.png" class="icon">
    </p>
    <div class="infos-footer">
        <div class="info">
            <img src="../images/Iconlocalisation.png" class="icon">
            <span>5 avenue de la république, 75015 Paris</span>
        </div>
        <div class="info">
            <img src="../images/Iconhorloge.png" class="icon">
            <span>Tous les jours 9h - 20h</span>
        </div>
    </div>
    <h5>© 2026 Pâtisserie</h5>
</footer>

<!-- Bouton mode sombre -->
<button id="btn-darkmode" class="btn-darkmode">☾</button>
<script src="../fichiers_js/darkmode.js"></script>

</body>
</html>
