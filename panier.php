<?php
session_start();

// Vérifier si utilisateur connecté
if (!isset($_SESSION['id'])) {
    header("Location: connexion.php");
    exit();
}

// Lire le panier
$panierFile = 'data/panier.json';
$panier = file_exists($panierFile) ? json_decode(file_get_contents($panierFile), true) : [];

// Charger tous les clients
$clientFile = 'data/infoclient.json';
$clients = file_exists($clientFile) ? json_decode(file_get_contents($clientFile), true) : [];

// Trouver l'utilisateur connecté via session
$id = $_SESSION['id'];
$client = null;
foreach ($clients as $c) {
    if ($c['id'] == $id) {
        $client = $c;
        break;
    }
}
$client = $client ?? [];

// Gestion suppression produit
if (isset($_POST['supprimer_index'])) {
    $index = (int) $_POST['supprimer_index'];
    array_splice($panier, $index, 1);
    file_put_contents($panierFile, json_encode($panier, JSON_PRETTY_PRINT));
    header("Location: panier.php");
    exit();
}

/* -------------------------------------------------------------
   NOUVEAU : quand l'utilisateur clique sur "Valider et payer",
   on recoit en POST le tableau des quantites a jour (rempli par
   le JS juste avant la soumission). On met a jour panier.json
   AVANT de calculer le total pour le paiement.
------------------------------------------------------------- */
if (isset($_POST['payer']) && isset($_POST['quantites']) && is_array($_POST['quantites'])) {
    foreach ($_POST['quantites'] as $index => $qte) {
        $qte = (int) $qte;
        if ($qte < 1) $qte = 1;
        if (isset($panier[$index])) {
            $panier[$index]['quantite'] = $qte;
        }
    }
    file_put_contents($panierFile, json_encode($panier, JSON_PRETTY_PRINT));
}

// Calcul du total (apres mise a jour eventuelle)
$total = 0;
foreach ($panier as $item) {
    $total += $item['prix'] * $item['quantite'];
}

// Type de commande
if (isset($_POST['type_commande'])) {
    $_SESSION['type_commande'] = $_POST['type_commande'];
    $type_commande = $_POST['type_commande'];
} else {
    $type_commande = $_SESSION['type_commande'] ?? 'sur_place';
}

// Déterminer date livraison finale
if ($type_commande === 'livraison') {
    $dateLivraisonFinale = $_SESSION['date_livraison'] ?? null;
} else {
    $dateLivraisonFinale = date('Y-m-d');
}

// Stocker infos dans session pour retour paiement
$_SESSION['commande_temp'] = [
    "type_commande" => $type_commande,
    "date_livraison" => $dateLivraisonFinale
];

// Paiement
require('getapikey.php');
if (isset($_POST['payer'])) {
    $transaction = uniqid();
    $montant = number_format($total, 2, '.', '');
    $vendeur = "MI-2_F";
    $retour = "http://localhost:8000/retour_paiement.php?session=s";
    $api_key = getAPIKey($vendeur);

    $control = md5(
        $api_key . "#" .
        $transaction . "#" .
        $montant . "#" .
        $vendeur . "#" .
        $retour . "#"
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>panier</title>
    <link rel="icon" type="images/png" href="images/logosite.png">
    <link rel="stylesheet" href="couleurs.css">
    <link rel="stylesheet" href="structg.css">
    <link rel="stylesheet" href="../pageproduit/pageproduit.css">
    <link rel="stylesheet" href="panier.css">
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

<div class="search-bar">
    <input type="search" placeholder=" qu'est-ce qui vous ferait plaisir?">
    <button><img src="images/Iconloupe.png" alt="loupe"></button>
</div>
<br>

<nav class="menu-horizontal">
    <ul>
        <li><a href="menu.html">Nos Menus</a></li>
        <li><a href="presentation.php" class="active">Tous nos produits</a></li>
    </ul>
</nav>

<h1 style="text-align:center; font-size: 40px;">Panier</h1>

<!--
   Conteneur des produits. Les inputs de quantite ne sont DANS
   AUCUN formulaire ici (sinon nested forms = invalide). On les
   recopie dynamiquement dans le formulaire de paiement au moment
   du clic "Valider et payer" via JavaScript.
-->
<div class="panier-produit-container">
    <?php foreach ($panier as $index => $item): ?>
    <div class="ligne-produit" data-prix="<?= $item['prix'] ?>" data-index="<?= $index ?>">
        <a href="pageproduit/produits.php?nom=<?= str_replace(' ', '', $item['produit']) ?>">
            <img src="images/<?= str_replace(' ', '', $item['produit']) ?>.jpg" alt="<?= $item['produit'] ?>" class="img-produit">
        </a>

        <div class="info-produit">
            <p><?= $item['produit'] ?></p>

            <!-- Plus de bouton "rafraichir". Juste un input number. -->
            <div class="quantite" id="nocolumn">
                <input type="number"
                       class="input-quantite"
                       data-index="<?= $index ?>"
                       value="<?= $item['quantite'] ?>"
                       min="1"
                       max="20">
            </div>

            <p>Saveur: <?= $item['saveur'] ?></p>
        </div>

        <!-- Prix de la ligne, recalcule en JS -->
        <p class="prix-produit">
            <span class="prix-ligne"><?= number_format($item['prix'] * $item['quantite'], 2, '.', '') ?></span> €
        </p>

        <!-- Suppression dans un form INDEPENDANT (pas imbrique) -->
        <form method="POST" style="display:inline;">
            <input type="hidden" name="supprimer_index" value="<?= $index ?>">
            <button type="submit" class="panier">Supprimer</button>
        </form>
    </div>
    <hr>
    <?php endforeach; ?>
</div>

<div class="total">
    <p>Total</p>
    <p><span id="total-panier"><?= number_format($total, 2, '.', '') ?></span> €</p>
</div>

<h2 style="text-align:center;">Commande</h2>

<!-- Form pour le choix sur_place / livraison (inchange) -->
<form method="POST" class="commande">
    <div class="ligne-commande">
        <div class="choix-commande">
            <label>
                <input type="radio" name="type_commande" value="sur_place"
                       <?= ($type_commande === 'sur_place') ? 'checked' : '' ?>
                       onchange="this.form.submit()">
                <img src="images/surplace.png" class="choix-img"/>
            </label>

            <label>
                <input type="radio" name="type_commande" value="livraison"
                       <?= ($type_commande === 'livraison') ? 'checked' : '' ?>
                       onchange="this.form.submit()">
                <img src="images/livraison.png" class="choix-img">
            </label>
        </div>

        <?php
        if (isset($_POST['date_livraison'])) {
             $_SESSION['date_livraison'] = $_POST['date_livraison'];
        }
        ?>

        <?php if ($type_commande === 'livraison'): ?>
            <div class="bloc-livraison">
                <h3>Informations pour la commande en livraison</h3>
                <p><strong>Nom :</strong> <?= htmlspecialchars($client['nom'] ?? '') ?></p>
                <p><strong>Prénom :</strong> <?= htmlspecialchars($client['prenom'] ?? '') ?></p>
                <p><strong>Téléphone :</strong> <?= htmlspecialchars($client['telephone'] ?? '') ?></p>
                <p><strong>Adresse :</strong> <?= htmlspecialchars($client['adresse'] ?? '') ?></p>
                <label>Date de livraison :</label>
                <input type="date" name="date_livraison" required>
            </div>
        <?php elseif ($type_commande === 'sur_place'): ?>
            <div class="bloc-livraison">
                <h3>Informations pour la commande sur place</h3>
                <p><strong>Nom :</strong> <?= htmlspecialchars($client['nom'] ?? '') ?></p>
                <p><strong>Prénom :</strong> <?= htmlspecialchars($client['prenom'] ?? '') ?></p>
                <p><strong>Téléphone :</strong> <?= htmlspecialchars($client['telephone'] ?? '') ?></p>
            </div>
        <?php endif; ?>
    </div>
</form>

<!--
   Form de paiement : c'est lui qu'on submit pour declencher le
   bouton "payer". Avant la soumission, le JS remplit ce form
   avec les quantites a jour (inputs hidden) pour que PHP puisse
   les enregistrer dans panier.json.
-->
<form method="POST" id="form-paiement">
    <!-- Les inputs hidden seront ajoutes par JS juste avant submit -->
    <button type="submit" name="payer" class="panier btn-commande">
        Valider et payer
    </button>
</form>

<?php if (isset($_POST['payer'])): ?>
<form id="cybankForm" action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
    <input type="hidden" name="transaction" value="<?= $transaction ?>">
    <input type="hidden" name="montant" value="<?= $montant ?>">
    <input type="hidden" name="vendeur" value="<?= $vendeur ?>">
    <input type="hidden" name="retour" value="<?= $retour ?>">
    <input type="hidden" name="control" value="<?= $control ?>">
</form>
<script>
    document.getElementById('cybankForm').submit();
</script>
<?php endif; ?>


<!-- ============================================================
     JAVASCRIPT : recalcul auto du prix de chaque ligne + total
     - Ecoute le changement de tous les inputs .input-quantite
     - Met a jour le prix de la ligne et le total general
     - Avant le submit du form de paiement, injecte les quantites
       sous forme d'inputs hidden quantites[index]
============================================================ -->
<script>
function recalculerTout() {
    let totalGeneral = 0;

    document.querySelectorAll('.ligne-produit').forEach(function(ligne) {
        const prixUnitaire = parseFloat(ligne.dataset.prix);
        const input = ligne.querySelector('.input-quantite');
        let qte = parseInt(input.value);
        if (isNaN(qte) || qte < 1) qte = 1;

        const sousTotal = prixUnitaire * qte;
        ligne.querySelector('.prix-ligne').textContent = sousTotal.toFixed(2);
        totalGeneral += sousTotal;
    });

    document.getElementById('total-panier').textContent = totalGeneral.toFixed(2);
}

// Recalculer a chaque changement
document.querySelectorAll('.input-quantite').forEach(function(input) {
    input.addEventListener('input', recalculerTout);
});

// Avant de soumettre le formulaire de paiement, on injecte les quantites
document.getElementById('form-paiement').addEventListener('submit', function() {
    const form = this;
    // Nettoyer d'eventuels anciens inputs hidden
    form.querySelectorAll('input.qte-hidden').forEach(e => e.remove());

    document.querySelectorAll('.input-quantite').forEach(function(input) {
        const index = input.dataset.index;
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'quantites[' + index + ']';
        hidden.value = input.value;
        hidden.className = 'qte-hidden';
        form.appendChild(hidden);
    });
});
</script>


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
</body>
</html>
