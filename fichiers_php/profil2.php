<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../fichiers_php/connexion.php");
    exit();
}

$userId = $_SESSION['id'];

/* Charger client */
$users = json_decode(file_get_contents("../data/infoclient.json"), true) ?? [];
$userTrouve = null;
foreach ($users as $u) {
    if ($u['id'] == $userId) { $userTrouve = $u; break; }
}
if (!$userTrouve) { die("Utilisateur introuvable."); }

/* Charger commandes (commande.json sans s) et produits */
$fichierCommandes = "../data/commande.json";
$commandes = json_decode(file_get_contents($fichierCommandes), true) ?? [];
$produits  = json_decode(file_get_contents("../data/produits.json"), true) ?? [];

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

/* Fige le montant deja paye AVANT une modif, pour calculer le supplement */
function figerMontantPaye(&$cmd) {
    if (!isset($cmd['montant_paye'])) {
        $cmd['montant_paye'] = $cmd['montant'];
    }
}

/* Compte les accompagnements d'une commande traiteur (tout sauf la piece montee) */
function compterAccompagnements($cmd) {
    $n = 0;
    foreach ($cmd['produits'] as $p) {
        if (($p['type'] ?? '') !== 'piece_montee') $n++;
    }
    return $n;
}

$messageInfo = null;
$NB_ACCOMP_MAX = 6;

/* =============================================================
   RETOUR DU PAIEMENT DE SUPPLEMENT (cybank renvoie en GET)
============================================================= */
if (isset($_GET['status']) && isset($_SESSION['supplement_profil'])) {

    $transaction  = $_GET['transaction'] ?? '';
    $montant      = $_GET['montant']     ?? '';
    $vendeur      = $_GET['vendeur']     ?? '';
    $status       = $_GET['status']      ?? '';
    $control_recu = $_GET['control']     ?? '';

    require('getapikey.php');
    $api_key = getAPIKey($vendeur);
    $control_calcule = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $status . "#");

    if ($control_recu === $control_calcule && $status === "accepted") {
        $refPayee = $_SESSION['supplement_profil']['ref'];
        foreach ($commandes as &$cmd) {
            if ($cmd['reference'] === $refPayee && commandeAppartient($cmd, $userTrouve)) {
                $cmd['montant_paye']       = $cmd['montant']; // a jour
                $cmd['modifie_par_client'] = true;
                $cmd['date_revalidation']  = date('Y-m-d H:i:s');
                break;
            }
        }
        unset($cmd);
        file_put_contents($fichierCommandes, json_encode($commandes, JSON_PRETTY_PRINT));
        $messageInfo = "Supplément payé. Votre commande est revalidée.";
    } else {
        $messageInfo = "Le paiement du supplément a échoué. Votre commande n'a pas été revalidée.";
    }
    unset($_SESSION['supplement_profil']);
}

/* =============================================================
   ACTIONS (POST)
============================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ref    = $_POST['ref']    ?? '';
    $action = $_POST['action'] ?? '';

    /* ---- Lancer le paiement du supplement ---- */
    if ($action === 'payer_supplement') {
        $cmdCible = null;
        foreach ($commandes as $c) {
            if ($c['reference'] === $ref && commandeAppartient($c, $userTrouve)) { $cmdCible = $c; break; }
        }
        if ($cmdCible) {
            $dejaPaye   = $cmdCible['montant_paye'] ?? $cmdCible['montant'];
            $supplement = round($cmdCible['montant'] - $dejaPaye, 2);

            if ($supplement > 0) {
                $_SESSION['supplement_profil'] = ['ref' => $ref];

                require('getapikey.php');
                $transaction = uniqid();
                $montant     = number_format($supplement, 2, '.', '');
                $vendeur     = "MI-2_F";

                $protocole = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
                $dossier   = rtrim(dirname($_SERVER['PHP_SELF']), '/');
                $retour    = $protocole . "://" . $_SERVER['HTTP_HOST'] . $dossier . "/profil2.php";

                $api_key = getAPIKey($vendeur);
                $control = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $retour . "#");
                ?>
                <!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Paiement</title></head>
                <body>
                <p>Redirection vers le paiement du supplément…</p>
                <form id="cybankForm" action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
                    <input type="hidden" name="transaction" value="<?= $transaction ?>">
                    <input type="hidden" name="montant"     value="<?= $montant ?>">
                    <input type="hidden" name="vendeur"     value="<?= $vendeur ?>">
                    <input type="hidden" name="retour"      value="<?= $retour ?>">
                    <input type="hidden" name="control"     value="<?= $control ?>">
                </form>
                <script>document.getElementById('cybankForm').submit();</script>
                </body></html>
                <?php
                exit();
            }
        }
        header("Location: ../fichiers_php/profil2.php");
        exit();
    }

    /* ---- Modifications de la commande ---- */
    foreach ($commandes as &$cmd) {
        if ($cmd['reference'] !== $ref) continue;
        if (!commandeAppartient($cmd, $userTrouve) || $cmd['statut'] !== "a preparer") {
            $messageInfo = "Cette commande ne peut plus être modifiée.";
            break;
        }

        $estTraiteur = (($cmd['type_commande'] ?? '') === 'traiteur');

        if ($action === 'modif_quantite') {
            $idx = (int) $_POST['index'];
            $qte = max(1, (int) $_POST['quantite']);
            // On ne modifie pas la piece montee
            if (isset($cmd['produits'][$idx]) && ($cmd['produits'][$idx]['type'] ?? '') !== 'piece_montee') {
                figerMontantPaye($cmd);
                $cmd['produits'][$idx]['quantite'] = (string) $qte;
                recalculerMontant($cmd);
                $cmd['modifie_par_client'] = true;
            }
        }
        elseif ($action === 'supprimer_produit') {
            $idx = (int) $_POST['index'];
            if (isset($cmd['produits'][$idx]) && ($cmd['produits'][$idx]['type'] ?? '') !== 'piece_montee') {
                figerMontantPaye($cmd);
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

            // Regles specifiques aux commandes traiteur
            if ($estTraiteur && $prod && ($prod['categorie'] ?? '') === 'Boissons') {
                $messageInfo = "Les boissons ne sont pas autorisées comme accompagnement.";
            }
            elseif ($estTraiteur && compterAccompagnements($cmd) >= $NB_ACCOMP_MAX) {
                $messageInfo = "Vous avez déjà atteint le maximum de $NB_ACCOMP_MAX accompagnements. Impossible d'en ajouter un de plus.";
            }
            elseif ($prod) {
                figerMontantPaye($cmd);
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
            $dejaPaye   = $cmd['montant_paye'] ?? $cmd['montant'];
            $supplement = round($cmd['montant'] - $dejaPaye, 2);
            if ($supplement > 0) {
                $messageInfo = "Vous devez d'abord régler le supplément de " . number_format($supplement, 2, ',', ' ') . " € avant de revalider.";
            } else {
                $cmd['modifie_par_client'] = true;
                $cmd['date_revalidation']  = date('Y-m-d H:i:s');
                $messageInfo = "Votre commande a été revalidée. Le cuisinier verra la mise à jour.";
            }
        }
        break;
    }
    unset($cmd);

    file_put_contents($fichierCommandes, json_encode($commandes, JSON_PRETTY_PRINT));

    // Pour les actions qui ne montrent pas de message, on redirige (anti-resoumission)
    if (!in_array($action, ['revalider', 'ajouter_produit'], true)) {
        header("Location: ../fichiers_php/profil2.php");
        exit();
    }
}

/* Filtrer les commandes de l'utilisateur connecte */
$mesCommandes = [];
foreach ($commandes as $cmd) {
    if (commandeAppartient($cmd, $userTrouve)) {
        $mesCommandes[] = $cmd;
    }
}
usort($mesCommandes, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

/* Notations deja faites */
$notations = [];
if (file_exists("../data/notations.json")) {
    $notations = json_decode(file_get_contents("../data/notations.json"), true) ?? [];
}
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
    <link rel="stylesheet" href="../fichiers_css/couleurs.css">
    <link rel="stylesheet" href="../fichiers_css/structg.css">
    <link rel="stylesheet" href="../fichiers_css/profil.css">
    <link rel="stylesheet" href="../fichiers_css/darkmode.css">
    <style>
        .message-info { max-width:900px; margin:15px auto; padding:12px 16px; border-radius:8px;
                        background:var(--succes-fond); color:var(--succes-texte); }
        .solde-du { background:var(--rouge-fond); color:var(--rouge-texte); padding:8px 12px;
                    border-radius:6px; margin:8px 0; font-weight:bold; }
        .btn-payer-supp { background:var(--accent); color:#fff; border:none; padding:10px 16px;
                          border-radius:8px; cursor:pointer; font-size:1em; }
        .btn-payer-supp:hover { background:var(--accent-fonce); }
        .piece-montee-fixe { font-style:italic; color:var(--texte-doux); padding:6px 0; }
    </style>
</head>
<body>

<header>
    <div class="barres"><span></span><span></span><span></span></div>
    <h1><a href="../fichiers_php/accueil.php" class="logo">La Cour des Délices</a></h1>
    <div class="top-icons">
        <div class="profil-menu">
            <img src="../images/Iconprofil.png" class="icon">
            <div class="profil-bulle">
                <a href="../fichiers_php/profil.php">Profil</a>
                <a href="../fichiers_php/logout.php">Déconnexion</a>
            </div>
        </div>
        <a href="../fichiers_php/panier.php"><img src="../images/Iconpanier.png" class="icon" id="panier"></a>
    </div>
</header>

<main class="container">
    <section style="width:100%;">
        <h2 style="text-align:center;">Mes commandes</h2>

        <?php if ($messageInfo): ?>
            <div class="message-info"><?= htmlspecialchars($messageInfo) ?></div>
        <?php endif; ?>

        <?php if (empty($mesCommandes)): ?>
            <p style="text-align:center;">Vous n'avez aucune commande pour le moment.</p>
        <?php else: ?>
            <table class="table-commandes">
                <tr>
                    <th>Référence</th>
                    <th>Date</th>
                    <th>Livraison / Événement</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Détails</th>
                </tr>

                <?php foreach ($mesCommandes as $cmd):
                    $statutClass = "statut-a-preparer";
                    $statutLabel = "À préparer";
                    if      ($cmd['statut'] === "en preparation")    { $statutClass = "statut-en-prep";   $statutLabel = "En préparation"; }
                    elseif  ($cmd['statut'] === "commande préparée") { $statutClass = "statut-prepare";   $statutLabel = "Préparée"; }
                    elseif  ($cmd['statut'] === "en_livraison")      { $statutClass = "statut-livraison"; $statutLabel = "En livraison"; }
                    elseif  ($cmd['statut'] === "terminee")          { $statutClass = "statut-terminee";  $statutLabel = "Livrée"; }

                    $modifiable  = ($cmd['statut'] === "a preparer");
                    $estTraiteur = (($cmd['type_commande'] ?? '') === 'traiteur');
                    $dejaPaye    = $cmd['montant_paye'] ?? $cmd['montant'];
                    $supplement  = round($cmd['montant'] - $dejaPaye, 2);
                    $idLigne     = "details-" . preg_replace('/[^a-zA-Z0-9]/', '', $cmd['reference']);
                ?>
                    <tr>
                        <td><?= htmlspecialchars($cmd['reference']) ?><?php if ($estTraiteur): ?> 🎂<?php endif; ?></td>
                        <td><?= date("d/m/Y", strtotime($cmd['date'])) ?></td>
                        <td><?= date("d/m/Y", strtotime($cmd['datelivraison'])) ?></td>
                        <td><?= number_format($cmd['montant'], 2) ?> €</td>
                        <td><span class="statut <?= $statutClass ?>"><?= $statutLabel ?></span></td>
                        <td><button class="bouton-details" data-cible="<?= $idLigne ?>">Voir</button></td>
                    </tr>

                    <tr id="<?= $idLigne ?>" class="ligne-details">
                        <td colspan="6">

                            <?php if ($estTraiteur && !empty($cmd['piece_montee'])): ?>
                                <p><strong>🎂 Pièce montée :</strong>
                                    <?= htmlspecialchars($cmd['piece_montee']['etages']) ?> étage(s),
                                    glaçage <?= htmlspecialchars($cmd['piece_montee']['glacage']) ?>,
                                    génoise <?= htmlspecialchars($cmd['piece_montee']['genoise']) ?>,
                                    garniture <?= htmlspecialchars($cmd['piece_montee']['garniture']) ?>
                                </p>
                            <?php endif; ?>

                            <table class="table-details">
                                <tr>
                                    <th>Produit</th><th>Saveur</th><th>Quantité</th>
                                    <th>Prix unitaire</th><th>Sous-total</th>
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

                            <p><strong><?= $estTraiteur ? "Lieu de l'événement" : "Adresse de livraison" ?> :</strong>
                               <?= htmlspecialchars($cmd['adresse']) ?></p>

                            <p><strong>Payé :</strong> <?= number_format($dejaPaye, 2) ?> € /
                               <strong>Total :</strong> <?= number_format($cmd['montant'], 2) ?> €</p>

                            <?php if ($supplement > 0): ?>
                                <div class="solde-du">Supplément à régler : <?= number_format($supplement, 2, ',', ' ') ?> €</div>
                            <?php endif; ?>

                            <?php if ($modifiable): ?>
                                <div class="zone-modif">
                                    <h3>Modifier cette commande</h3>
                                    <p><em>Vous pouvez modifier tant que le cuisinier n'a pas commencé la préparation.</em></p>
                                    <?php if ($estTraiteur): ?>
                                        <p><em>Commande traiteur : <?= $NB_ACCOMP_MAX ?> accompagnements maximum (hors boissons).</em></p>
                                    <?php endif; ?>

                                    <?php foreach ($cmd['produits'] as $idx => $prod):
                                        $estPM = (($prod['type'] ?? '') === 'piece_montee');
                                    ?>
                                        <div class="produit-modif">
                                            <span class="nom-prod"><?= htmlspecialchars($prod['produit']) ?></span>
                                            <?php if ($estPM): ?>
                                                <span class="piece-montee-fixe">(pièce montée — non modifiable)</span>
                                            <?php else: ?>
                                                <form method="POST" class="form-inline">
                                                    <input type="hidden" name="ref" value="<?= htmlspecialchars($cmd['reference']) ?>">
                                                    <input type="hidden" name="action" value="modif_quantite">
                                                    <input type="hidden" name="index" value="<?= $idx ?>">
                                                    <input type="number" name="quantite" value="<?= $prod['quantite'] ?>" min="1" max="200" onchange="this.form.submit()">
                                                </form>
                                                <form method="POST" class="form-inline" onsubmit="return confirm('Retirer ce produit ?');">
                                                    <input type="hidden" name="ref" value="<?= htmlspecialchars($cmd['reference']) ?>">
                                                    <input type="hidden" name="action" value="supprimer_produit">
                                                    <input type="hidden" name="index" value="<?= $idx ?>">
                                                    <button type="submit">Retirer</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>

                                    <hr>

                                    <!-- Ajouter un produit / accompagnement -->
                                    <form method="POST">
                                        <input type="hidden" name="ref"    value="<?= htmlspecialchars($cmd['reference']) ?>">
                                        <input type="hidden" name="action" value="ajouter_produit">
                                        <strong><?= $estTraiteur ? "Ajouter un accompagnement :" : "Ajouter :" ?></strong>
                                        <select name="nom_produit" required>
                                            <option value="">-- produit --</option>
                                            <?php foreach ($produits as $p):
                                                if ($estTraiteur && ($p['categorie'] ?? '') === 'Boissons') continue;
                                            ?>
                                                <option value="<?= htmlspecialchars($p['nom']) ?>">
                                                    <?= htmlspecialchars($p['titre']) ?> (<?= number_format($p['prix'], 2) ?>€)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="text" name="saveur" placeholder="Saveur">
                                        <input type="number" name="quantite" value="1" min="1" max="200">
                                        <button type="submit">+</button>
                                    </form>

                                    <hr>

                                    <?php if ($supplement > 0): ?>
                                        <!-- Paiement obligatoire du supplement -->
                                        <form method="POST">
                                            <input type="hidden" name="ref" value="<?= htmlspecialchars($cmd['reference']) ?>">
                                            <input type="hidden" name="action" value="payer_supplement">
                                            <button type="submit" class="btn-payer-supp">
                                                Payer le supplément (<?= number_format($supplement, 2, ',', ' ') ?> €)
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <!-- Revalidation simple (rien a payer) -->
                                        <form method="POST">
                                            <input type="hidden" name="ref" value="<?= htmlspecialchars($cmd['reference']) ?>">
                                            <input type="hidden" name="action" value="revalider">
                                            <button type="submit" class="btn-revalider">Revalider la commande</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <p><em>Cette commande ne peut plus être modifiée.</em></p>
                            <?php endif; ?>

                            <?php $dejaNoted = isset($notationsParRef[$cmd['reference']]); ?>
                            <?php if ($cmd['statut'] === 'terminee'): ?>
                                <?php if ($dejaNoted): ?>
                                    <div style="margin-top:8px;">
                                        <?php
                                            $noteSat = $notationsParRef[$cmd['reference']]['satisfaction']['note'];
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= $noteSat
                                                    ? '<span style="color:var(--article-background);font-size:1.2em;">★</span>'
                                                    : '<span style="color:#ccc;font-size:1.2em;">★</span>';
                                            }
                                        ?>
                                        <a href="../fichiers_php/notation.php?ref=<?= urlencode($cmd['reference']) ?>" style="margin-left:8px;">Voir mon avis</a>
                                    </div>
                                <?php else: ?>
                                    <a href="../fichiers_php/notation.php?ref=<?= urlencode($cmd['reference']) ?>">Noter la commande</a>
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
        <img src="../images/Iconinstagram.jpg" class="icon">
        <img src="../images/Icontiktok.jpg" class="icon">
        <img src="../images/Icontwitter.png" class="icon">
    </p>
    <div class="infos-footer">
        <div class="info"><img src="../images/Iconlocalisation.png" class="icon"><span>5 av de la république, 75300 Paris</span></div>
        <div class="info"><img src="../images/Iconhorloge.png" class="icon"><span>Tous les jours 9h - 22h</span></div>
    </div>
    <h5>© 2026 Pâtisserie</h5>
</footer>

<script>
document.querySelectorAll('.bouton-details').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const cible = document.getElementById(this.dataset.cible);
        if (cible) cible.classList.toggle('ouverte');
    });
});
</script>

<button id="btn-darkmode" class="btn-darkmode">☾</button>
<script src="../fichiers_js/darkmode.js"></script>
</body>
</html>
