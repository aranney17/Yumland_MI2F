<?php
session_start();

/* check bloque + check role  */
if (isset($_SESSION['id'])) {
    $usersCheck = json_decode(file_get_contents("../data/infoclient.json"), true) ?? [];
    foreach ($usersCheck as $u) {
        if ($u['id'] == $_SESSION['id']) {
            if ($u['bloque'] ?? false) {
                session_destroy();
                die("Votre compte a été bloqué. <a href='../connexion.php'>Retour</a>");
            }
            /*if ($u['role'] !== 'client') {
                $redir = [
                    'cuisinier'      => '../fichiers_php/commandes.php',
                    'livreur'        => '../fichiers_php/livraison.php',
                    'administrateur' => '../fichiers_php/administrateur.php'
                ];
                header("Location: " . ($redir[$u['role']] ?? '../fichiers_php/accueil.php'));
                exit();
            }*/
            break;
        }
    }
}

// Recuperer le produit dans l'URL
$nom = $_GET['nom'] ?? null;
$fichierProduits = '../data/produits.json';
$produits = json_decode(file_get_contents($fichierProduits), true);

$produitActuel = null;
foreach ($produits as $p) {
    if ($p['nom'] === $nom) { $produitActuel = $p; break; }
}
if (!$produitActuel) {
    echo "Produit introuvable";
    exit();
}

// Ajout au panier
if (isset($_POST['ajouter_panier'])) {
    $produit  = $_POST['produit'];
    $prix     = $_POST['prix'];
    $saveur   = $_POST['saveur'];
    $quantite = $_POST['quantite'];

    $fichier = '../data/panier.json';
    $panier = file_exists($fichier) ? json_decode(file_get_contents($fichier), true) : [];
    $panier[] = [
        "produit"  => $produit,
        "prix"     => $prix,
        "saveur"   => $saveur,
        "quantite" => $quantite
    ];
    file_put_contents($fichier, json_encode($panier, JSON_PRETTY_PRINT));
    header("Location: ../fichiers_php/produits.php?nom=" . $produitActuel['nom']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($produitActuel['titre']) ?></title>
    <link rel="icon" type="../images/png" href="../images/logosite.png">
    <link rel="stylesheet" href="../fichiers_css/couleurs.css">
    <link rel="stylesheet" href="../fichiers_css/structg.css">
    <link rel="stylesheet" href="../fichiers_css/pageproduit.css">
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
        <?php if (isset($_SESSION["connecte"]) && $_SESSION["connecte"]): ?>
            <a href="../fichiers_php/profil.php">Mon profil</a>
            <a href="../fichiers_php/profil2.php">Mes commandes</a>
            <a href="../fichiers_php/logout.php">Se déconnecter</a>
        <?php else: ?>
            <a href="../fichiers_php/inscription.php">S'inscrire</a>
            <a href="../fichiers_php/connexion.php">Connexion</a>
        <?php endif; ?>
        </div>
    </div>
    <a href="../fichiers_php/panier.php"><img src="../images/Iconpanier.png" alt="Panier" class="icon" id="panier"></a>
</div>

<form class="search-bar" action="../fichiers_php/recherche.php" method="get" autocomplete="off">
    <input type="search" name="q" placeholder=" qu'est-ce qui vous ferait plaisir?">
    <button type="submit"><img src="../images/Iconloupe.png" alt="loupe"></button>
</form>

<nav class="breadcrumb">
    <a href="../fichiers_php/accueil.php">Accueil</a>
    <span>></span>
    <a href="../fichiers_php/presentation.php">Tous nos produits</a>
    <span>></span>
    <a href="../fichiers_php/presentation.php#<?= strtolower($produitActuel['categorie']) ?>">
        <?= $produitActuel['categorie'] ?>
    </a>
    <span>></span>
    <span class="page-actuelle"><?= $produitActuel['titre'] ?></span>
</nav>

<div class="produit-page">
    <div class="produit-image">
        <img src="../images/<?= $produitActuel['image'] ?>" alt="<?= $produitActuel['titre'] ?>">
    </div>

    <div class="produit-info">
        <h1 id="prod"><?= $produitActuel['titre'] ?></h1>
        <p class="prix"><?= number_format($produitActuel['prix'], 2, ',', ' ') ?> €</p>

        <form action="" method="POST" class="quantite">
            <input type="hidden" name="produit" value="<?= $produitActuel['titre'] ?>">
            <input type="hidden" name="prix"    value="<?= $produitActuel['prix'] ?>">

            <label>Quantité :</label><br>
            <input type="number" name="quantite" value="1" min="1" max="20">

            <label>Saveur :</label><br>
            <select name="saveur">
                <?php foreach ($produitActuel['saveurs'] as $saveur): ?>
                    <option value="<?= $saveur ?>"><?= $saveur ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" name="ajouter_panier" class="panier">
                Ajouter au panier
            </button>
        </form>
    </div>
</div>

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
