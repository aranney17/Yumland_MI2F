<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: connexion.php");
    exit();
}

$panierFile = '../data/panier.json';
$panier = file_exists($panierFile) ? json_decode(file_get_contents($panierFile), true) : [];

$clientFile = '../data/infoclient.json';
$clients = file_exists($clientFile) ? json_decode(file_get_contents($clientFile), true) : [];

$id = $_SESSION['id'];
$client = null;
foreach ($clients as $c) {
    if ($c['id'] == $id) { $client = $c; break; }
}
$client = $client ?? [];

/* Sauvegarde silencieuse des quantites (AJAX) */
if (isset($_POST['action_sauvegarde']) && isset($_POST['quantites']) && is_array($_POST['quantites'])) {
    foreach ($_POST['quantites'] as $idx => $qte) {
        $qte = max(1, (int)$qte);
        if (isset($panier[$idx])) {
            $panier[$idx]['quantite'] = $qte;
        }
    }
    file_put_contents($panierFile, json_encode($panier, JSON_PRETTY_PRINT));
    http_response_code(204);
    exit();
}

/* Suppression produit */
if (isset($_POST['supprimer_index'])) {
    $index = (int) $_POST['supprimer_index'];
    array_splice($panier, $index, 1);
    file_put_contents($panierFile, json_encode($panier, JSON_PRETTY_PRINT));
    header("Location: panier.php");
    exit();
}

/* Calcul total (un menu a aussi prix * quantite) */
$total = 0;
foreach ($panier as $item) {
    $total += $item['prix'] * $item['quantite'];
}

/* Type commande */
if (isset($_POST['type_commande'])) {
    $_SESSION['type_commande'] = $_POST['type_commande'];
    $type_commande = $_POST['type_commande'];
} else {
    $type_commande = $_SESSION['type_commande'] ?? 'sur_place';
}

if ($type_commande === 'livraison') {
    $dateLivraisonFinale = $_SESSION['date_livraison'] ?? null;
} else {
    $dateLivraisonFinale = date('Y-m-d');
}

$_SESSION['commande_temp'] = [
    "type_commande"  => $type_commande,
    "date_livraison" => $dateLivraisonFinale
];

/* Paiement */
require('getapikey.php');
if (isset($_POST['payer'])) {
    $transaction = uniqid();
    $montant = number_format($total, 2, '.', '');
    $vendeur = "MI-2_F";

    /* URL de retour DYNAMIQUE.
       panier.php et retour_paiement.php sont dans le MEME dossier
       (fichiers_php/). dirname() donne deja "/fichiers_php", il suffit
       d'ajouter "/retour_paiement.php". PAS de "../fichiers_php/". */
    $protocole = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $dossier   = rtrim(dirname($_SERVER['PHP_SELF']), '/');
    $retour    = $protocole . "://" . $_SERVER['HTTP_HOST'] . $dossier . "/retour_paiement.php";

    $api_key = getAPIKey($vendeur);
    $control = md5(
        $api_key . "#" . $transaction . "#" . $montant . "#" .
        $vendeur . "#" . $retour . "#"
    );
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier</title>
    <link rel="icon" type="../images/png" href="../images/logosite.png">
    <link rel="stylesheet" href="../fichiers_css/couleurs.css">
    <link rel="stylesheet" href="../fichiers_css/structg.css">
    <link rel="stylesheet" href="../fichiers_css/pageproduit.css">
    <link rel="stylesheet" href="../fichiers_css/panier.css">
    <link rel="stylesheet" href="../fichiers_css/darkmode.css">
    <style>
        .badge-menu {
            display:flex; align-items:center; justify-content:center;
            width:90px; height:90px; border-radius:10px;
            background: var(--article-background); color:#fff;
            font-weight:bold; font-size:14px; text-align:center;
        }
        .composition-menu { font-size:14px; margin-top:4px; }
        .composition-menu span { display:block; }
        .etiquette-menu {
            display:inline-block; font-size:11px; background:var(--article-background);
            color:#fff; padding:1px 8px; border-radius:10px; margin-left:6px;
            vertical-align:middle;
        }
    </style>
</head>
<body>

<div class="barres"><span></span><span></span><span></span></div>

<h1><a href="accueil.php" class="logo">La Cour des Délices</a></h1>

<div class="top-icons">
    <div class="profil-menu">
        <img src="../images/Iconprofil.png" class="icon">
        <div class="profil-bulle">
            <a href="profil.php">Profil</a>
            <a href="logout.php">Déconnexion</a>
        </div>
    </div>
    <a href="panier.php"><img src="../images/Iconpanier.png" class="icon" id="panier"></a>
</div>

<form class="search-bar" action="../fichiers_php/recherche.php" method="get" autocomplete="off">
    <input type="search" name="q" placeholder=" qu'est-ce qui vous ferait plaisir?">
    <button type="submit"><img src="../images/Iconloupe.png" alt="loupe"></button>
</form>
<br>

<nav class="menu-horizontal">
    <ul>
        <li><a href="menu.php">Nos Menus</a></li>
        <li><a href="presentation.php" class="active">Tous nos produits</a></li>
    </ul>
</nav>

<h1 style="text-align:center; font-size: 40px;">Panier</h1>

<div class="panier-produit-container">
    <?php foreach ($panier as $index => $item):
        $estMenu = (isset($item['type']) && $item['type'] === 'menu');
    ?>
    <div class="ligne-produit" data-prix="<?= $item['prix'] ?>" data-index="<?= $index ?>">

        <?php if ($estMenu): ?>
            <!-- ===== AFFICHAGE D'UN MENU ===== -->
            <div class="badge-menu">MENU</div>

            <div class="info-produit">
                <p>
                    <strong><?= htmlspecialchars($item['nom_menu']) ?></strong>
                    <span class="etiquette-menu">Menu</span>
                </p>
                <div class="composition-menu">
                    <?php foreach ($item['composition'] as $label => $choix): ?>
                        <span><?= htmlspecialchars($label) ?> : <strong><?= htmlspecialchars($choix) ?></strong></span>
                    <?php endforeach; ?>
                </div>
                <div class="quantite" id="nocolumn">
                    <input type="number" class="input-quantite" data-index="<?= $index ?>"
                           value="<?= $item['quantite'] ?>" min="1" max="50">
                </div>
            </div>

        <?php else: ?>
            <!-- ===== AFFICHAGE D'UN PRODUIT SIMPLE ===== -->
            <a href="../pageproduit/produits.php?nom=<?= str_replace(' ', '', $item['produit']) ?>">
                <img src="../images/<?= str_replace(' ', '', $item['produit']) ?>.jpg"
                     alt="<?= htmlspecialchars($item['produit']) ?>" class="img-produit">
            </a>

            <div class="info-produit">
                <p><?= htmlspecialchars($item['produit']) ?></p>
                <div class="quantite" id="nocolumn">
                    <input type="number" class="input-quantite" data-index="<?= $index ?>"
                           value="<?= $item['quantite'] ?>" min="1" max="50">
                </div>
                <p>Saveur: <?= htmlspecialchars($item['saveur'] ?? '') ?></p>
            </div>
        <?php endif; ?>

        <p class="prix-produit">
            <span class="prix-ligne"><?= number_format($item['prix'] * $item['quantite'], 2, '.', '') ?></span> €
        </p>

        <form method="POST" style="display:inline;">
            <input type="hidden" name="supprimer_index" value="<?= $index ?>">
            <button type="submit" class="panier">Supprimer</button>
        </form>
    </div>
    <hr>
    <?php endforeach; ?>

    <?php if (count($panier) === 0): ?>
        <p style="text-align:center;">Votre panier est vide.</p>
    <?php endif; ?>
</div>

<div class="total">
    <p>Total</p>
    <p><span id="total-panier"><?= number_format($total, 2, '.', '') ?></span> €</p>
</div>

<h2 style="text-align:center;">Commande</h2>

<form method="POST" class="commande" id="form-type-commande">
    <div class="ligne-commande">
        <div class="choix-commande">
            <label>
                <input type="radio" name="type_commande" value="sur_place"
                       <?= ($type_commande === 'sur_place') ? 'checked' : '' ?>
                       onchange="soumettreFormType()">
                <img src="../images/surplace.png" class="choix-img"/>
            </label>
            <label>
                <input type="radio" name="type_commande" value="livraison"
                       <?= ($type_commande === 'livraison') ? 'checked' : '' ?>
                       onchange="soumettreFormType()">
                <img src="../images/livraison.png" class="choix-img">
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
        <?php else: ?>
            <div class="bloc-livraison">
                <h3>Informations pour la commande sur place</h3>
                <p><strong>Nom :</strong> <?= htmlspecialchars($client['nom'] ?? '') ?></p>
                <p><strong>Prénom :</strong> <?= htmlspecialchars($client['prenom'] ?? '') ?></p>
                <p><strong>Téléphone :</strong> <?= htmlspecialchars($client['telephone'] ?? '') ?></p>
            </div>
        <?php endif; ?>
    </div>
</form>

<form method="POST" id="form-paiement">
    <button type="submit" name="payer" class="panier btn-commande">Valider et payer</button>
</form>

<?php if (isset($_POST['payer'])): ?>
<form id="cybankForm" action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
    <input type="hidden" name="transaction" value="<?= $transaction ?>">
    <input type="hidden" name="montant"     value="<?= $montant ?>">
    <input type="hidden" name="vendeur"     value="<?= $vendeur ?>">
    <input type="hidden" name="retour"      value="<?= $retour ?>">
    <input type="hidden" name="control"     value="<?= $control ?>">
</form>
<script>document.getElementById('cybankForm').submit();</script>
<?php endif; ?>

<script>
function recalculerTout() {
    let totalGeneral = 0;
    document.querySelectorAll('.ligne-produit').forEach(function(ligne) {
        const prixUnitaire = parseFloat(ligne.dataset.prix);
        const input = ligne.querySelector('.input-quantite');
        if (!input) return;
        let qte = parseInt(input.value);
        if (isNaN(qte) || qte < 1) qte = 1;
        const sousTotal = prixUnitaire * qte;
        ligne.querySelector('.prix-ligne').textContent = sousTotal.toFixed(2);
        totalGeneral += sousTotal;
    });
    document.getElementById('total-panier').textContent = totalGeneral.toFixed(2);
}

function sauvegarderQuantites() {
    const formData = new FormData();
    formData.append('action_sauvegarde', '1');
    document.querySelectorAll('.input-quantite').forEach(function(input) {
        formData.append('quantites[' + input.dataset.index + ']', input.value);
    });
    return fetch('panier.php', { method: 'POST', body: formData });
}

document.querySelectorAll('.input-quantite').forEach(function(input) {
    input.addEventListener('input', function() {
        recalculerTout();
        sauvegarderQuantites();
    });
});

function soumettreFormType() {
    sauvegarderQuantites().then(function() {
        document.getElementById('form-type-commande').submit();
    });
}

document.getElementById('form-paiement').addEventListener('submit', function() {
    const form = this;
    form.querySelectorAll('input.qte-hidden').forEach(e => e.remove());
    document.querySelectorAll('.input-quantite').forEach(function(input) {
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'quantites[' + input.dataset.index + ']';
        hidden.value = input.value;
        hidden.className = 'qte-hidden';
        form.appendChild(hidden);
    });
});
</script>

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
<script src="../fichiers_js/recherche.js"></script>
</body>
</html>
