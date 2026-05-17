<?php
session_start();

if (!isset($_SESSION['id'])) {
    die("Utilisateur non connecté ou session expirée.");
}
$userId = $_SESSION['id'];

/* Recuperer parametres cybank */
$transaction  = $_GET['transaction'] ?? '';
$montant      = $_GET['montant']     ?? '';
$vendeur      = $_GET['vendeur']     ?? '';
$status       = $_GET['status']      ?? '';
$control_recu = $_GET['control']     ?? '';

/* Recalcul du controle */
require('getapikey.php');
$api_key = getAPIKey($vendeur);
$control_calcule = md5(
    $api_key . "#" . $transaction . "#" . $montant . "#" .
    $vendeur . "#" . $status . "#"
);

$controleOK      = ($control_recu === $control_calcule);
$paiementAccepte = ($controleOK && $status === "accepted");

/* -------------------------------------------------------------
   DETECTER LE TYPE DE RETOUR :
   - Si $_SESSION['paiement_supplement'] est defini : retour
     d'un paiement de supplement (modification de commande)
   - Sinon : retour du paiement initial d'une nouvelle commande
------------------------------------------------------------- */
$estPaiementSupplement = isset($_SESSION['paiement_supplement']);

$fichierPanier    = 'data/panier.json';
$fichierClients   = 'data/infoclient.json';
$fichierCommandes = 'data/commande.json';


if ($estPaiementSupplement) {

    /* =========================================================
       CAS 1 : PAIEMENT D'UN SUPPLEMENT (modification commande)
    ========================================================= */
    $infoSup = $_SESSION['paiement_supplement'];
    $refCmd  = $infoSup['ref'];
    $sup     = floatval($infoSup['supplement']);

    if ($paiementAccepte) {
        // Mettre a jour montant_paye de la commande
        $commandes = file_exists($fichierCommandes)
            ? json_decode(file_get_contents($fichierCommandes), true)
            : [];

        foreach ($commandes as &$cmd) {
            if ($cmd['reference'] === $refCmd) {
                $ancienPaye = floatval($cmd['montant_paye'] ?? $cmd['montant']);
                $cmd['montant_paye']       = round($ancienPaye + $sup, 2);
                $cmd['modifie_par_client'] = true;
                $cmd['date_revalidation']  = date('Y-m-d H:i:s');
                break;
            }
        }
        unset($cmd);
        file_put_contents($fichierCommandes, json_encode($commandes, JSON_PRETTY_PRINT));
    }
    // En cas d'echec : on ne touche pas a la commande, les modifs
    // restent appliquees mais montant_paye reste figé → l'utilisateur
    // verra toujours l'alerte "supplement a payer" en revenant sur profil2.

    unset($_SESSION['paiement_supplement']);

} else {

    /* =========================================================
       CAS 2 : PAIEMENT INITIAL D'UNE NOUVELLE COMMANDE
    ========================================================= */
    if ($paiementAccepte) {

        // Charger client
        $clients = file_exists($fichierClients)
            ? json_decode(file_get_contents($fichierClients), true) : [];
        $client = [];
        foreach ($clients as $c) {
            if ($c['id'] == $userId) { $client = $c; break; }
        }

        // Charger panier
        $panier = file_exists($fichierPanier)
            ? json_decode(file_get_contents($fichierPanier), true) : [];

        $type_commande = $_SESSION['commande_temp']['type_commande'] ?? 'sur_place';
        $dateLivraison = $_SESSION['commande_temp']['date_livraison'] ?? date('Y-m-d');

        $total = 0;
        foreach ($panier as $p) {
            $total += $p['prix'] * $p['quantite'];
        }
        $total = round($total, 2);

        $reference = strtoupper(substr(md5(uniqid()), 0, 10));

        $commande = [
            "id"                => time(),
            "nom"               => $client['nom']       ?? '',
            "prenom"            => $client['prenom']    ?? '',
            "date"              => date('Y-m-d'),
            "datelivraison"     => $dateLivraison,
            "type_commande"     => $type_commande,
            "telephone"         => $client['telephone'] ?? '',
            "reference"         => $reference,
            "montant"           => $total,
            "montant_paye"      => $total,  // initialisation : tout est paye
            "paiement"          => "payee",
            "adresse"           => $client['adresse']   ?? '',
            "statut"            => "a preparer",
            "notif_vue"         => false,
            "modifie_par_client"=> false,
            "produits"          => $panier
        ];

        $commandes = file_exists($fichierCommandes)
            ? json_decode(file_get_contents($fichierCommandes), true) : [];
        $commandes[] = $commande;
        file_put_contents($fichierCommandes, json_encode($commandes, JSON_PRETTY_PRINT));

        // Vider panier
        file_put_contents($fichierPanier, json_encode([], JSON_PRETTY_PRINT));
        unset($_SESSION['date_livraison'], $_SESSION['commande_temp']);

    } else {
        // Paiement initial echoue : vider panier (demande Haroun)
        file_put_contents($fichierPanier, json_encode([], JSON_PRETTY_PRINT));
        unset($_SESSION['date_livraison'], $_SESSION['commande_temp']);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Retour paiement</title>
    <link rel="stylesheet" href="panier.css">
    <link rel="stylesheet" href="couleurs.css">
    <link rel="stylesheet" href="structg.css">
    <link rel="stylesheet" href="../pageproduit/pageproduit.css">
    <link rel="stylesheet" href="darkmode.css">
</head>
<body>

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

<div class="confirmation">

    <?php if (!$controleOK): ?>
        <h1>Erreur de sécurité !</h1>
        <p>Les données de paiement sont invalides. Aucune modification n'a été appliquée.</p>

    <?php elseif ($paiementAccepte): ?>
        <h1>Merci, votre paiement a bien été validé !</h1>

        <?php if ($estPaiementSupplement): ?>
            <p>Le supplément de votre commande a été payé. Le cuisinier verra la mise à jour.</p>
        <?php else: ?>
            <p>Votre commande a bien été enregistrée.</p>
        <?php endif; ?>

        <ul class="bloc-livraison">
            <li><u>Référence de transaction :</u> <?= htmlspecialchars($transaction) ?></li>
            <li><u>Montant payé :</u> <?= htmlspecialchars($montant) ?> €</li>
            <li><u>Statut :</u> <?= htmlspecialchars($status) ?></li>
        </ul>

    <?php else: ?>
        <h1>Paiement refusé !</h1>
        <?php if ($estPaiementSupplement): ?>
            <p>Le paiement du supplément n'a pas abouti. Vos modifications restent en attente, vous pouvez réessayer depuis votre historique.</p>
        <?php else: ?>
            <p>Votre paiement n'a pas été accepté. Votre panier a été vidé.</p>
        <?php endif; ?>
    <?php endif; ?>

    <a href="<?= $estPaiementSupplement ? 'profil2.php' : 'accueil.php' ?>" class="panier btn-commande">
        <?= $estPaiementSupplement ? "Retour à mes commandes" : "Revenir à la boutique" ?>
    </a>

</div>

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
