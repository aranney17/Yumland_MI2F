<?php
session_start();
$userId = $_SESSION['id'] ?? null;
if (!$userId) {
    die("Utilisateur non connecté ou session expirée.");
}

// Récupérer les paramètres envoyés par CYBank
$transaction = $_GET['transaction'] ?? '';
$montant = $_GET['montant'] ?? '';
$vendeur = $_GET['vendeur'] ?? '';
$status = $_GET['status'] ?? '';
$control_recu = $_GET['control'] ?? '';

// Recalcul du contrôle
require('getapikey.php');
$api_key = getAPIKey($vendeur);
$control_calcule = md5(
    $api_key . "#" .
    $transaction . "#" .
    $montant . "#" .
    $vendeur . "#" .
    $status . "#"
);

// Vérifier utilisateur
if (!isset($_SESSION['id'])) {
    die("Utilisateur non connecté ou session expirée.");
}
$userId = $_SESSION['id'];

// Charger client
$clients = file_exists('data/infoclient.json') ? json_decode(file_get_contents('data/infoclient.json'), true) : [];
$client = null;
foreach ($clients as $c) {
    if ($c['id'] == $userId) {
        $client = $c;
        break;
    }
}
$client = $client ?? [];

// Récupérer le panier
$panier = file_exists('data/panier.json') ? json_decode(file_get_contents('data/panier.json'), true) : [];

// Récupérer date livraison et type commande depuis session
$type_commande = $_SESSION['commande_temp']['type_commande'] ?? 'sur_place';
$dateLivraison = $_SESSION['commande_temp']['date_livraison'] ?? date('Y-m-d'); // fallback date du jour

// Calcul total
$total = 0;
foreach ($panier as $p) {
    $total += $p['prix'] * $p['quantite'];
}

// Générer référence et date commande
$reference = strtoupper(substr(md5(uniqid()), 0, 10));
$dateCommande = date('Y-m-d');

// Créer commande complète
$commande = [
    "id" => time(),
    "nom" => $client['nom'] ?? '',
    "prenom" => $client['prenom'] ?? '',
    "date" => $dateCommande,
    "datelivraison" => $dateLivraison,
    "type_commande" => $type_commande, // nouveau champ
    "telephone" => $client['telephone'] ?? '',
    "reference" => $reference,
    "montant" => $total,
    "paiement" => "payee",
    "adresse" => $client['adresse'] ?? '',
    "statut" => "preparation",
    "produits" => $panier
];

// Sauvegarder commandes
$fichier = 'data/commande.json';
$commandes = file_exists($fichier) ? json_decode(file_get_contents($fichier), true) : [];
$commandes[] = $commande;
file_put_contents($fichier, json_encode($commandes, JSON_PRETTY_PRINT));

// Vider panier et nettoyer session
file_put_contents('data/panier.json', json_encode([], JSON_PRETTY_PRINT));
unset($_SESSION['date_livraison'], $_SESSION['commande_temp']);
?>

<?php
// ... ton code existant ...

$userId = $_GET['id'] ?? null; // récupérer l'ID passé dans l'URL

// Charger le fichier des clients
$clients = file_exists('data/infoclient.json') ? json_decode(file_get_contents('data/infoclient.json'), true) : [];
$client = null;

// Trouver le client correspondant
if ($userId) {
    foreach ($clients as $c) {
        if ($c['id'] == $userId) {
            $client = $c;
            break;
        }
    }
}

// Si aucun client trouvé, fallback sur tableau vide
$client = $client ?? [];
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
        <?php if ($control_recu !== $control_calcule): ?>
            <h1>Erreur de sécurité !</h1>
            <p>Les données de paiement sont invalides.</p>
        <?php elseif ($status === "accepted"): ?>
    <h1>Merci pour votre commande !</h1>
    <ul class="bloc-livraison">
        <li><u>Référence de commande :</u> <?= htmlspecialchars($transaction) ?></li>
        <li><u>Montant payé :</u> <?= htmlspecialchars($montant) ?> €</li>
        <li><u>Vendeur :</u> <?= htmlspecialchars($vendeur) ?></li>
        <li><u>Statut :</u> <?= htmlspecialchars($status) ?></li>
    </ul>

     <?php else: ?>
            <h1>Paiement refusé !</h1>
            <p>Votre paiement n’a pas été accepté. Veuillez réessayer.</p>
        <?php endif; ?>

        <!-- Bouton pour revenir à la boutique -->
        
        <a href="accueil.php" class="panier btn-commande">Revenir à la boutique</a>
        
    </div>


    <footer>
<p>suivez nous sur nos réseaux!
    </br>
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
</body>
</html>
