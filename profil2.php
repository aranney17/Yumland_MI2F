<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: connexion.php");
    exit();
}

$userId = $_SESSION['id'];

/* Charger client */
$users = json_decode(file_get_contents("data/infoclient.json"), true) ?? [];
$userTrouve = null;
foreach ($users as $u) {
    if ($u['id'] == $userId) { $userTrouve = $u; break; }
}
if (!$userTrouve) { die("Utilisateur introuvable."); }

/* Charger commandes et produits.
   CORRECTION : c'est commande.json (sans s), pas commandes.json */
$fichierCommandes = "data/commande.json";
$commandes = json_decode(file_get_contents($fichierCommandes), true) ?? [];

$produits = json_decode(file_get_contents("data/produits.json"), true) ?? [];


/* -------------------------------------------------------------
   HELPERS
------------------------------------------------------------- */
function commandeAppartient($cmd, $user) {
    return $cmd['nom']       === $user['nom']
        && $cmd['prenom']    === $user['prenom']
        && $cmd['telephone'] === $user['telephone'];
}

function recalculerMontant(&$cmd) {
    $t = 0;
    foreach ($cmd['produits'] as $p) {
        $t += floatval($p['prix']) * intval($p['quantite']);
    }
    $cmd['montant'] = $t;
}


/* -------------------------------------------------------------
   ACTIONS POST : modif quantite / suppression / ajout / revalidation
   Toutes restreintes aux commandes "a preparer" du client connecte
------------------------------------------------------------- */
$messageInfo = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ref    = $_POST['ref']    ?? '';
    $action = $_POST['action'] ?? '';

    foreach ($commandes as &$cmd) {
        if ($cmd['reference'] !== $ref) continue;
        if (!commandeAppartient($cmd, $userTrouve) || $cmd['statut'] !== "a preparer") {
            $messageInfo = "Cette commande ne peut plus être modifiée.";
            break;
        }

        if ($action === 'modif_quantite') {
            $idx = (int) $_POST['index'];
            $qte = max(1, (int) $_POST['quantite']);
            if (isset($cmd['produits'][$idx])) {
                $cmd['produits'][$idx]['quantite'] = (string) $qte;
                recalculerMontant($cmd);
                $cmd['modifie_par_client'] = true;
            }
        }
        elseif ($action === 'supprimer_produit') {
            $idx = (int) $_POST['index'];
            if (isset($cmd['produits'][$idx])) {
                array_splice($cmd['produits'], $idx, 1);
                recalculerMontant($cmd);
                $cmd['modifie_par_client'] = true;
            }
        }
        elseif ($action === 'ajouter_produit') {
            $nomProduit = $_POST['nom_produit'] ?? '';
            $saveur     = $_POST['saveur']      ?? '';
            $qte        = max(1, (int) $_POST['quantite']);

            $prod = null;
            foreach ($produits as $p) {
                if ($p['nom'] === $nomProduit) { $prod = $p; break; }
            }
            if ($prod) {
                $cmd['produits'][] = [
                    "produit"  => $prod['titre'],
                    "prix"     => (string) $prod['prix'],
                    "saveur"   => $saveur ?: ($prod['saveurs'][0] ?? ''),
                    "quantite" => (string) $qte
                ];
                recalculerMontant($cmd);
                $cmd['modifie_par_client'] = true;
            }
        }
        elseif ($action === 'revalider') {
            // Bouton "Revalider" : on confirme les modifs et on
            // met une date pour que le cuisinier sache que la
            // commande a ete touchee recemment.
            $cmd['modifie_par_client'] = true;
            $cmd['date_revalidation']  = date('Y-m-d H:i:s');
            $messageInfo = "Votre commande a été revalidée. Le cuisinier verra la mise à jour.";
        }
        break;
    }
    unset($cmd);

    file_put_contents($fichierCommandes, json_encode($commandes, JSON_PRETTY_PRINT));

    // Si ce n'est pas un revalider, on redirige (pour eviter resoumission au refresh)
    if ($action !== 'revalider') {
        header("Location: profil2.php");
        exit();
    }
}


/* -------------------------------------------------------------
   Filtrer les commandes de l'utilisateur connecte
------------------------------------------------------------- */
$mesCommandes = [];
foreach ($commandes as $cmd) {
    if (commandeAppartient($cmd, $userTrouve)) {
        $mesCommandes[] = $cmd;
    }
}

// Trier par date decroissante
usort($mesCommandes, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Charger les notations pour savoir lesquelles ont déjà été notées
$notations = [];
if (file_exists("data/notations.json")) {
    $notations = json_decode(file_get_contents("data/notations.json"), true) ?? [];
}
 
// Indexer par référence pour accès rapide
$notationsParRef = [];
foreach ($notations as $notation) {
    if ($notation['id_utilisateur'] == $_SESSION['id']) {
        $notationsParRef[$notation['reference']] = $notation;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique de commandes</title>
    <link rel="stylesheet" href="couleurs.css">
    <link rel="stylesheet" href="structg.css">
    <link rel="stylesheet" href="profil.css">
    <link rel="stylesheet" href="darkmode.css">
    <style>
        .table-commandes { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table-commandes th, .table-commandes td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        .table-details { width: 100%; margin: 10px 0; background: #fafafa; }
        body.dark .table-details { background: #2d231c; }

        .ligne-details { display: none; }
        .ligne-details.ouverte { display: table-row; }

        .statut {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            color: white;
            font-size: 12px;
        }
        .statut-a-preparer  { background: #f0a040; }
        .statut-en-prep     { background: #4080f0; }
        .statut-prepare     { background: #8040f0; }
        .statut-livraison   { background: #f04040; }
        .statut-terminee    { background: #40b040; }

        .zone-modif { padding: 15px; background: #fff8e1; border: 1px dashed #f0a040; margin-top: 10px; }
        body.dark .zone-modif { background: #2d231c; border-color: #d4a574; }

        .produit-modif { display: flex; align-items: center; gap: 10px; padding: 5px 0; }
        .produit-modif .nom-prod { flex: 1; }
        .produit-modif input[type=number] { width: 60px; }
        .form-inline { display: inline; }

        .btn-revalider {
            background: #40b040;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        .message-info {
            background: #fff8e1;
            border: 1px solid #cc9;
            padding: 10px;
            margin: 15px 0;
        }
    </style>
</head>
<body>

<header>
    <div class="barres"><span></span><span></span><span></span></div>
    <h1><a href="accueil.php" class="logo">La Cour des Délices</a></h1>
    <div class="top-icons">
        <div class="profil-menu">
            <img src="images/Iconprofil.png" class="icon">
            <div class="profil-bulle">
                <a href="profil.php">Profil</a>
                <a href="logout.php">Déconnexion</a>
            </div>
        </div>
        <a href="panier.php"><img src="images/Iconpanier.png" class="icon" id="panier"></a>
    </div>
</header>

<main class="container">
    <aside class="sidebar">
        <ul class="menu">
            <li><a href="profil.php">Informations</a></li>
            <li><a href="profil2.php"><strong>Historique de commandes</strong></a></li>
            <li>Données personnelles</li>
        </ul>
        <br>
        <a href="logout.php"><p class="logout">Déconnexion</p></a>
    </aside>

    <section>
        <h2>Mes commandes</h2>

        <?php if ($messageInfo): ?>
            <div class="message-info"><?= htmlspecialchars($messageInfo) ?></div>
        <?php endif; ?>

        <?php if (empty($mesCommandes)): ?>
            <p>Vous n'avez pas encore passé de commande.</p>
        <?php else: ?>

            <table class="table-commandes">
                <tr>
                    <th>Référence</th>
                    <th>Date</th>
                    <th>Livraison</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Détails</th>
                </tr>

                <?php foreach ($mesCommandes as $cmd):
                    // Statut visuel
                    $statutClass = "statut-a-preparer";
                    $statutLabel = "À préparer";
                    if      ($cmd['statut'] === "en preparation")    { $statutClass = "statut-en-prep";   $statutLabel = "En préparation"; }
                    elseif  ($cmd['statut'] === "commande préparée") { $statutClass = "statut-prepare";   $statutLabel = "Préparée"; }
                    elseif  ($cmd['statut'] === "en_livraison")      { $statutClass = "statut-livraison"; $statutLabel = "En livraison"; }
                    elseif  ($cmd['statut'] === "terminee")          { $statutClass = "statut-terminee";  $statutLabel = "Livrée"; }

                    $modifiable = ($cmd['statut'] === "a preparer");
                    $idLigne = "details-" . preg_replace('/[^a-zA-Z0-9]/', '', $cmd['reference']);
                ?>
                    <!-- Ligne principale -->
                    <tr>
                        <td><?= htmlspecialchars($cmd['reference']) ?></td>
                        <td><?= date("d/m/Y", strtotime($cmd['date'])) ?></td>
                        <td><?= date("d/m/Y", strtotime($cmd['datelivraison'])) ?></td>
                        <td><?= number_format($cmd['montant'], 2) ?> €</td>
                        <td><span class="statut <?= $statutClass ?>"><?= $statutLabel ?></span></td>
                        <td><button class="bouton-details" data-cible="<?= $idLigne ?>">Voir</button></td>
                    </tr>

                    <!-- Ligne details (cachee par defaut) -->
                    <tr id="<?= $idLigne ?>" class="ligne-details">
                        <td colspan="6">

                            <table class="table-details">
                                <tr>
                                    <th>Produit</th>
                                    <th>Saveur</th>
                                    <th>Quantité</th>
                                    <th>Prix unitaire</th>
                                    <th>Sous-total</th>
                                </tr>
                                <?php foreach ($cmd['produits'] as $produit): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($produit['produit']) ?></td>
                                        <td><?= htmlspecialchars($produit['saveur'] ?? '—') ?></td>
                                        <td><?= $produit['quantite'] ?></td>
                                        <td><?= number_format($produit['prix'], 2) ?> €</td>
                                        <td><?= number_format($produit['prix'] * $produit['quantite'], 2) ?> €</td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>

                            <p><strong>Adresse de livraison :</strong> <?= htmlspecialchars($cmd['adresse']) ?></p>

                            <?php if ($modifiable): ?>

                                <!-- ============================
                                     ZONE DE MODIFICATION
                                ============================ -->
                                <div class="zone-modif">

                                    <h3>Modifier cette commande</h3>
                                    <p><em>Vous pouvez modifier tant que le cuisinier n'a pas commencé la préparation.</em></p>

                                    <?php foreach ($cmd['produits'] as $idx => $prod): ?>
                                        <div class="produit-modif">
                                            <span class="nom-prod"><?= htmlspecialchars($prod['produit']) ?></span>

                                            <!-- Modif quantite -->
                                            <form method="POST" class="form-inline">
                                                <input type="hidden" name="ref" value="<?= htmlspecialchars($cmd['reference']) ?>">
                                                <input type="hidden" name="action" value="modif_quantite">
                                                <input type="hidden" name="index" value="<?= $idx ?>">
                                                <input type="number" name="quantite" value="<?= $prod['quantite'] ?>" min="1" max="20" onchange="this.form.submit()">
                                            </form>

                                            <!-- Suppression -->
                                            <form method="POST" class="form-inline" onsubmit="return confirm('Retirer ce produit ?');">
                                                <input type="hidden" name="ref" value="<?= htmlspecialchars($cmd['reference']) ?>">
                                                <input type="hidden" name="action" value="supprimer_produit">
                                                <input type="hidden" name="index" value="<?= $idx ?>">
                                                <button type="submit">Retirer</button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>

                                    <hr>

                                    <!-- Ajouter un produit -->
                                    <form method="POST">
                                        <input type="hidden" name="ref"    value="<?= htmlspecialchars($cmd['reference']) ?>">
                                        <input type="hidden" name="action" value="ajouter_produit">

                                        <strong>Ajouter :</strong>
                                        <select name="nom_produit" required>
                                            <option value="">-- produit --</option>
                                            <?php foreach ($produits as $p): ?>
                                                <option value="<?= htmlspecialchars($p['nom']) ?>">
                                                    <?= htmlspecialchars($p['titre']) ?> (<?= number_format($p['prix'], 2) ?>€)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="text" name="saveur" placeholder="Saveur">
                                        <input type="number" name="quantite" value="1" min="1" max="20">
                                        <button type="submit">+</button>
                                    </form>

                                    <hr>

                                    <!-- Revalider la commande -->
                                    <form method="POST">
                                        <input type="hidden" name="ref" value="<?= htmlspecialchars($cmd['reference']) ?>">
                                        <input type="hidden" name="action" value="revalider">
                                        <button type="submit" class="btn-revalider">
                                            Revalider la commande
                                        </button>
                                    </form>
                                </div>

                            <?php else: ?>
                                <p><em>Cette commande ne peut plus être modifiée.</em></p>
                            <?php endif; ?>
                            
                            <?php
                                    $ref = $commande['reference'];
                                    $dejaNoted = isset($notationsParRef[$ref]);
                                ?>
 
                                <?php if ($commande['statut'] === 'livre') : ?>
                                    <?php if ($dejaNoted) : ?>
                                        <!-- Affiche la note de satisfaction + lien pour voir le détail -->
                                        <div style="margin-top: 8px;">
                                            <?php
                                                $noteSat = $notationsParRef[$ref]['satisfaction']['note'];
                                                for ($i = 1; $i <= 5; $i++) {
                                                    echo $i <= $noteSat
                                                    ? '<span style="color:var(--article-background);font-size:1.2em;">★</span>'
                                                    : '<span style="color:#ccc;font-size:1.2em;">★</span>';
                                                }
                                            ?>
                                            <a href="notation.php?ref=<?= $ref ?>" style="margin-left: 8px;">Voir mon avis</a>
                                        </div>
                                    <?php else : ?>
                                        <a href="notation.php?ref=<?= $ref ?>">Noter la commande</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                        </td>
                    </tr>

                <?php endforeach; ?>
            </table>

        <?php endif; ?>
    </section>
</main>

<footer>
    <p>suivez nous sur nos réseaux!<br>
        <img src="images/Iconinstagram.jpg" class="icon">
        <img src="images/Icontiktok.jpg" class="icon">
        <img src="images/Icontwitter.png" class="icon">
    </p>
    <div class="infos-footer">
        <div class="info"><img src="images/Iconlocalisation.png" class="icon"><span>5 av de la république, 75300 Paris</span></div>
        <div class="info"><img src="images/Iconhorloge.png" class="icon"><span>Tous les jours 9h - 22h</span></div>
    </div>
    <h5>© 2026 Pâtisserie</h5>
</footer>

<!-- JS pour ouvrir/fermer les details -->
<script>
document.querySelectorAll('.bouton-details').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const cible = document.getElementById(this.dataset.cible);
        if (cible) cible.classList.toggle('ouverte');
    });
});
</script>

<button id="btn-darkmode" class="btn-darkmode">☾</button>
<script src="darkmode.js"></script>
</body>
</html>
