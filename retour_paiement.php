<?php
session_start();

/* -------------------------------------------------------------
   1. VERIFICATIONS PRELIMINAIRES
------------------------------------------------------------- */
if (!isset($_SESSION['id'])) {
    die("Utilisateur non connecté ou session expirée.");
}
$userId = $_SESSION['id'];

/* -------------------------------------------------------------
   2. RECUPERATION DES PARAMETRES RENVOYES PAR CYBANK
------------------------------------------------------------- */
$transaction  = $_GET['transaction'] ?? '';
$montant      = $_GET['montant']     ?? '';
$vendeur      = $_GET['vendeur']     ?? '';
$status       = $_GET['status']      ?? '';
$control_recu = $_GET['control']     ?? '';

/* -------------------------------------------------------------
   3. RECALCUL DU CONTROLE (verifie que les donnees n'ont pas
      ete falsifiees)
------------------------------------------------------------- */
require('getapikey.php');
$api_key = getAPIKey($vendeur);
$control_calcule = md5(
    $api_key . "#" .
    $transaction . "#" .
    $montant . "#" .
    $vendeur . "#" .
    $status . "#"
);

/* -------------------------------------------------------------
   4. DETERMINATION DU RESULTAT
      - controleOK       : les donnees sont authentiques
      - paiementAccepte  : controle OK ET status = "accepted"
------------------------------------------------------------- */
$controleOK      = ($control_recu === $control_calcule);
$paiementAccepte = ($controleOK && $status === "accepted");


/* -------------------------------------------------------------
   5. SI PAIEMENT ACCEPTE : on enregistre la commande, on vide
      le panier, on nettoie la session.
      SINON : on vide le panier (demande de Haroun) et on
      n'enregistre RIEN dans commande.json.
------------------------------------------------------------- */
$fichierPanier    = 'data/panier.json';
$fichierClients   = 'data/infoclient.json';
$fichierCommandes = 'data/commande.json';

if ($paiementAccepte) {

    // Charger le client connecte
    $clients = file_exists($fichierClients)
        ? json_decode(file_get_contents($fichierClients), true)
        : [];
    $client = [];
    foreach ($clients as $c) {
        if ($c['id'] == $userId) { $client = $c; break; }
    }

    // Charger le panier (les produits commandes)
    $panier = file_exists($fichierPanier)
        ? json_decode(file_get_contents($fichierPanier), true)
        : [];

    // Recuperer infos commande temporaires
    $type_commande = $_SESSION['commande_temp']['type_commande'] ?? 'sur_place';
    $dateLivraison = $_SESSION['commande_temp']['date_livraison'] ?? date('Y-m-d');

    // Calcul total
    $total = 0;
    foreach ($panier as $p) {
        $total += $p['prix'] * $p['quantite'];
    }

    // Generer reference
    $reference = strtoupper(substr(md5(uniqid()), 0, 10));

    // Creer commande
    $commande = [
        "id"            => time(),
        "nom"           => $client['nom']       ?? '',
        "prenom"        => $client['prenom']    ?? '',
        "date"          => date('Y-m-d'),
        "datelivraison" => $dateLivraison,
        "type_commande" => $type_commande,
        "telephone"     => $client['telephone'] ?? '',
        "reference"     => $reference,
        "montant"       => $total,
        "paiement"      => "payee",
        "adresse"       => $client['adresse']   ?? '',
        "statut"        => "a preparer",
        "notif_vue"     => false, // pour la notification cote client
        "produits"      => $panier
    ];

    // Enregistrer la commande
    $commandes = file_exists($fichierCommandes)
        ? json_decode(file_get_contents($fichierCommandes), true)
        : [];
    $commandes[] = $commande;
    file_put_contents($fichierCommandes, json_encode($commandes, JSON_PRETTY_PRINT));

    // Vider le panier et nettoyer la session
    file_put_contents($fichierPanier, json_encode([], JSON_PRETTY_PRINT));
    unset($_SESSION['date_livraison'], $_SESSION['commande_temp']);

} else {
    // PAIEMENT REFUSE OU CONTROLE INVALIDE
    // On vide le panier (demande explicite) et on nettoie la session.
    // AUCUNE commande n'est enregistree.
    file_put_contents($fichierPanier, json_encode([], JSON_PRETTY_PRINT));
    unset($_SESSION['date_livraison'], $_SESSION['commande_temp']);
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
    <div class="barres">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <h1> <a href="accueil.php" class="logo">La Cour des Délices</a></h1>

<div class="top-icons">
    <div class="profil-menu">
        <img src="images/Iconprofil.png" alt="Profil" class="icon">
        <div class="profil-bulle">
            <a href="inscription.php">Inscription</a>
            <a href="connexion.php">Connexion</a>
        </div>
    </div>

    <a href="panier.php">
        <img src="images/Iconpanier.png" alt="Panier" class="icon" id="panier">
    </a>
</div>

<div class="confirmation">

    <?php if (!$controleOK): ?>

        <!-- Cas 1 : donnees corrompues / falsifiees -->
        <h1>Erreur de sécurité !</h1>
        <p>Les données de paiement sont invalides. Aucune commande n'a été enregistrée.</p>

    <?php elseif ($paiementAccepte): ?>

        <!-- Cas 2 : paiement validé -->
        <h1>Merci pour votre commande !</h1>
        <p>Votre paiement a bien été validé.</p>
        <ul class="bloc-livraison">
            <li><u>Référence de transaction :</u> <?= htmlspecialchars($transaction) ?></li>
            <li><u>Montant payé :</u> <?= htmlspecialchars($montant) ?> €</li>
            <li><u>Vendeur :</u> <?= htmlspecialchars($vendeur) ?></li>
            <li><u>Statut :</u> <?= htmlspecialchars($status) ?></li>
        </ul>

    <?php else: ?>

        <!-- Cas 3 : paiement refusé -->
        <h1>Paiement refusé !</h1>
        <p>Votre paiement n'a pas été accepté. Aucune commande n'a été enregistrée et votre panier a été vidé.</p>

    <?php endif; ?>

    <!-- Bouton commun aux 3 cas pour revenir a la boutique -->
    <a href="accueil.php" class="panier btn-commande">Revenir à la boutique</a>

</div>

<footer>
<p>suivez nous sur nos réseaux!
    <br>
        <img src="images/Iconinstagram.jpg" alt="instagram" class="icon">
        <img src="images/Icontiktok.jpg" alt="tiktok" class="icon">
        <img src="images/Icontwitter.png" alt="twitter" class="icon">
</p>

<div class="infos-footer">
    <div class="info">
        <img src="images/Iconlocalisation.png" alt="maps" class="icon">
        <span>5 avenue de la république, 75015 Paris</span>
    </div>
    <div class="info">
        <img src="images/Iconhorloge.png" alt="horloge" class="icon">
        <span>Tous les jours 9h - 20h</span>
    </div>
</div>
       <h5>© 2026 Pâtisserie</h5>
</footer>
<button id="btn-darkmode" class="btn-darkmode">☾</button>
<script src="darkmode.js"></script>
</body>
</html>
