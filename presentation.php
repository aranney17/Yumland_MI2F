<?php
// Lire tous les produits
$fichierProduits = 'data/produits.json';
$produits = json_decode(file_get_contents($fichierProduits), true);

/* Ajout au panier (inchange) */
if (isset($_POST['ajouter_panier'])) {
    $nom      = $_POST['nom'];
    $produit  = $_POST['produit'];
    $prix     = $_POST['prix'];<?php
session_start();

/* -------------------------------------------------------------
   AJOUT : si on est connecte, on verifie qu'on n'est pas bloque
   et que le role est bien "client" (sinon redirection vers la
   page principale du role).
   Le check bloque DETRUIT la session et affiche un message.
------------------------------------------------------------- */
if (isset($_SESSION['id'])) {
    $usersCheck = json_decode(file_get_contents("data/infoclient.json"), true) ?? [];
    foreach ($usersCheck as $u) {
        if ($u['id'] == $_SESSION['id']) {
            if ($u['bloque'] ?? false) {
                session_destroy();
                die("Votre compte a été bloqué. <a href='connexion.php'>Retour</a>");
            }
            if ($u['role'] !== 'client') {
                $redir = [
                    'cuisinier'      => 'commandes.php',
                    'livreur'        => 'livraison.php',
                    'administrateur' => 'administrateur.php'
                ];
                header("Location: " . ($redir[$u['role']] ?? 'accueil.php'));
                exit();
            }
            break;
        }
    }
}

/* Lecture produits */
$fichierProduits = 'data/produits.json';
$produits = json_decode(file_get_contents($fichierProduits), true);

/* Ajout au panier */
if (isset($_POST['ajouter_panier'])) {
    $nom      = $_POST['nom'];
    $produit  = $_POST['produit'];
    $prix     = $_POST['prix'];
    $saveur   = $_POST['saveur'];
    $quantite = $_POST['quantite'];

    $fichier = 'data/panier.json';
    $panier = file_exists($fichier) ? json_decode(file_get_contents($fichier), true) : [];
    $panier[] = [
        "nom" => $nom, "produit" => $produit, "prix" => $prix,
        "saveur" => $saveur, "quantite" => $quantite
    ];
    file_put_contents($fichier, json_encode($panier, JSON_PRETTY_PRINT));
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

/* Regrouper par categorie */
$produitsParCategorie = [];
foreach ($produits as $p) {
    $cat = $p['categorie'];
    if (!isset($produitsParCategorie[$cat])) $produitsParCategorie[$cat] = [];
    $produitsParCategorie[$cat][] = $p;
}
$listeCategories = array_keys($produitsParCategorie);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tous nos produits</title>
    <link rel="stylesheet" href="couleurs.css">
    <link rel="stylesheet" href="structg.css">
    <link rel="stylesheet" href="cssaccueil.css">
    <link rel="stylesheet" href="darkmode.css">
    <style>
        .barre-filtres {
            max-width: 900px; margin: 20px auto; padding: 15px;
            background: #f5f5f5; border-radius: 8px;
            display: flex; justify-content: center; gap: 30px; flex-wrap: wrap;
        }
        .barre-filtres label { font-weight: bold; margin-right: 8px; }
        .barre-filtres select { padding: 6px 10px; }
        body.dark .barre-filtres { background: #2a2a2a; }
    </style>
</head>
<body>

<div class="barres"><span></span><span></span><span></span></div>
<h1><a href="accueil.php" class="logo">La Cour des Délices</a></h1>

<div class="top-icons">
    <div class="profil-menu">
        <img src="images/Iconprofil.png" alt="Profil" class="icon">
        <div class="profil-bulle">
        <?php if (isset($_SESSION["connecte"]) && $_SESSION["connecte"]): ?>
            <a href="profil.php">Mon profil</a>
            <a href="profil2.php">Mes commandes</a>
            <a href="logout.php">Se déconnecter</a>
        <?php else: ?>
            <a href="inscription.php">S'inscrire</a>
            <a href="connexion.php">Connexion</a>
        <?php endif; ?>
        </div>
    </div>
    <a href="panier.php"><img src="images/Iconpanier.png" alt="Panier" class="icon" id="panier"></a>
</div>

<div class="search-bar">
    <input type="search" placeholder=" qu'est-ce qui vous ferait plaisir?">
    <button><img src="images/Iconloupe.png" alt="loupe"></button>
</div>
<br>

<nav class="menu-horizontal">
    <ul>
        <li><a href="menu.html">Menus</a></li>
        <li><a href="presentation.php" class="active">Tous nos produits</a></li>
    </ul>
</nav>

<div class="barre-filtres">
    <div>
        <label for="filtre-tri">Trier par prix :</label>
        <select id="filtre-tri">
            <option value="defaut">Par défaut</option>
            <option value="croissant">Prix croissant</option>
            <option value="decroissant">Prix décroissant</option>
        </select>
    </div>
    <div>
        <label for="filtre-categorie">Catégorie :</label>
        <select id="filtre-categorie">
            <option value="toutes">Toutes</option>
            <?php foreach ($listeCategories as $cat): ?>
                <option value="<?= strtolower($cat) ?>"><?= $cat ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="categories">
    <a href="presentation.php#viennoiseries" class="categorie">
        <img src="images/Croissantaubeurre.jpg" alt="viennoiserie"><p>Viennoiseries</p>
    </a>
    <a href="presentation.php#boissons" class="categorie">
        <img src="images/Cafélatte.jpg" alt="Boissons"><p>Boissons</p>
    </a>
    <a href="presentation.php#gourmandises" class="categorie">
        <img src="images/Cookiegourmand.jpg" alt="Gourmandises"><p>Gourmandises</p>
    </a>
    <a href="presentation.php#patisseries" class="categorie">
        <img src="images/Eclairchocolat.jpg" alt="Patisseries"><p>Patisseries</p>
    </a>
    <a href="presentation.php#gateaux" class="categorie">
        <img src="images/Gateauauxfraises.jpg" alt="Gateaux"><p>Gateaux</p>
    </a>
    <a href="presentation.php#tartes" class="categorie">
        <img src="images/Tarteàlanoisette.jpg" alt="Tartes"><p>Tartes</p>
    </a>
</div>

<a href="" id="btn-top">↑</a>

<?php foreach ($produitsParCategorie as $categorie => $produitsCat):
    $idAncre = strtolower($categorie);
?>
    <section class="<?= $idAncre ?>" data-categorie="<?= $idAncre ?>">
        <h2 class="titre-section" id="<?= $idAncre ?>"><?= $categorie ?></h2>
        <div class="produits">
            <?php foreach ($produitsCat as $p): ?>
                <div class="produit" data-prix="<?= $p['prix'] ?>">
                    <a href="pageproduit/produits.php?nom=<?= $p['nom'] ?>">
                        <img src="images/<?= $p['image'] ?>" alt="<?= $p['titre'] ?>">
                    </a>
                    <div class="produit-info">
                        <div class="panier-menu">
                            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">
                            <div class="panier-bulle">
                                <form method="POST">
                                    <input type="hidden" name="nom"     value="<?= $p['nom'] ?>">
                                    <input type="hidden" name="produit" value="<?= $p['titre'] ?>">
                                    <input type="hidden" name="prix"    value="<?= number_format($p['prix'], 2) ?>">
                                    <label>Quantité :</label>
                                    <input type="number" name="quantite" value="1" min="1" max="20">
                                    <label>Saveur :</label>
                                    <select name="saveur">
                                        <?php foreach ($p['saveurs'] as $saveur): ?>
                                            <option value="<?= $saveur ?>"><?= $saveur ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="ajouter_panier">Ajouter</button>
                                </form>
                            </div>
                        </div>
                        <div>
                            <h3><?= $p['titre'] ?></h3>
                            <p class="prix"><?= number_format($p['prix'], 2, ',', ' ') ?>€</p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>

<script>
const selectTri       = document.getElementById('filtre-tri');
const selectCategorie = document.getElementById('filtre-categorie');

const ordreOriginal = {};
document.querySelectorAll('section[data-categorie]').forEach(function(section) {
    const cat = section.dataset.categorie;
    const conteneur = section.querySelector('.produits');
    ordreOriginal[cat] = Array.from(conteneur.children);
});

function appliquerFiltres() {
    const tri      = selectTri.value;
    const catChoix = selectCategorie.value;

    document.querySelectorAll('section[data-categorie]').forEach(function(section) {
        const cat = section.dataset.categorie;
        if (catChoix !== 'toutes' && catChoix !== cat) {
            section.style.display = 'none';
            return;
        }
        section.style.display = '';

        const conteneur = section.querySelector('.produits');
        let produits;
        if (tri === 'defaut') {
            produits = ordreOriginal[cat].slice();
        } else {
            produits = Array.from(conteneur.children).sort(function(a, b) {
                const prixA = parseFloat(a.dataset.prix);
                const prixB = parseFloat(b.dataset.prix);
                return tri === 'croissant' ? prixA - prixB : prixB - prixA;
            });
        }
        produits.forEach(p => conteneur.appendChild(p));
    });
}

selectTri.addEventListener('change', appliquerFiltres);
selectCategorie.addEventListener('change', appliquerFiltres);
</script>

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

    $saveur   = $_POST['saveur'];
    $quantite = $_POST['quantite'];

    $fichier = 'data/panier.json';
    $panier = file_exists($fichier) ? json_decode(file_get_contents($fichier), true) : [];

    $panier[] = [
        "nom" => $nom, "produit" => $produit, "prix" => $prix,
        "saveur" => $saveur, "quantite" => $quantite
    ];

    file_put_contents($fichier, json_encode($panier, JSON_PRETTY_PRINT));
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

/* Regrouper par categorie */
$produitsParCategorie = [];
foreach ($produits as $p) {
    $cat = $p['categorie'];
    if (!isset($produitsParCategorie[$cat])) $produitsParCategorie[$cat] = [];
    $produitsParCategorie[$cat][] = $p;
}

// Liste des categories pour le filtre
$listeCategories = array_keys($produitsParCategorie);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tous nos produits</title>
    <link rel="stylesheet" href="couleurs.css">
    <link rel="stylesheet" href="structg.css">
    <link rel="stylesheet" href="cssaccueil.css">
    <link rel="stylesheet" href="darkmode.css">
    <style>
        /* Barre de filtres */
        .barre-filtres {
            max-width: 900px;
            margin: 20px auto;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        .barre-filtres label { font-weight: bold; margin-right: 8px; }
        .barre-filtres select { padding: 6px 10px; }
        body.dark .barre-filtres { background: #2a2a2a; }
    </style>
</head>
<body>

<div class="barres"><span></span><span></span><span></span></div>

<h1><a href="accueil.php" class="logo">La Cour des Délices</a></h1>

<div class="top-icons">
    <div class="profil-menu">
        <img src="images/Iconprofil.png" alt="Profil" class="icon">
        <div class="profil-bulle">
        <?php if (isset($_SESSION["connecte"]) && $_SESSION["connecte"]): ?>
            <a href="profil.php">Mon profil</a>
            <a href="mescommandes.php">Mes commandes</a>
            <a href="logout.php">Se déconnecter</a>
        <?php else : ?>
            <a href="inscription.php">S'Inscrire</a>
            <a href="connexion.php">Connexion</a>
        <?php endif; ?>
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
        <li><a href="menu.html">Menus</a></li>
        <li><a href="presentation.php" class="active">Tous nos produits</a></li>
    </ul>
</nav>


<!-- ============================================================
     BARRE DE FILTRES
============================================================ -->
<div class="barre-filtres">
    <div>
        <label for="filtre-tri">Trier par prix :</label>
        <select id="filtre-tri">
            <option value="defaut">Par défaut</option>
            <option value="croissant">Prix croissant</option>
            <option value="decroissant">Prix décroissant</option>
        </select>
    </div>

    <div>
        <label for="filtre-categorie">Catégorie :</label>
        <select id="filtre-categorie">
            <option value="toutes">Toutes</option>
            <?php foreach ($listeCategories as $cat): ?>
                <option value="<?= strtolower($cat) ?>"><?= $cat ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>


<div class="categories">
    <a href="presentation.php#viennoiseries" class="categorie">
        <img src="images/Croissantaubeurre.jpg" alt="viennoiserie"><p>Viennoiseries</p>
    </a>
    <a href="presentation.php#boissons" class="categorie">
        <img src="images/Cafélatte.jpg" alt="Boissons"><p>Boissons</p>
    </a>
    <a href="presentation.php#gourmandises" class="categorie">
        <img src="images/Cookiegourmand.jpg" alt="Gourmandises"><p>Gourmandises</p>
    </a>
    <a href="presentation.php#patisseries" class="categorie">
        <img src="images/Eclairchocolat.jpg" alt="Patisseries"><p>Patisseries</p>
    </a>
    <a href="presentation.php#gateaux" class="categorie">
        <img src="images/Gateauauxfraises.jpg" alt="Gateaux"><p>Gateaux</p>
    </a>
    <a href="presentation.php#tartes" class="categorie">
        <img src="images/Tarteàlanoisette.jpg" alt="Tartes"><p>Tartes</p>
    </a>
</div>

<a href="" id="btn-top">↑</a>


<!-- ============================================================
     SECTIONS PRODUITS
     Pour permettre le tri JS, j'ai mis tous les produits d'une
     categorie dans UN SEUL conteneur .produits (au lieu de les
     decouper 2 par 2). Le CSS de .produits (flex/grid) gerera
     l'affichage en lignes naturellement.
============================================================ -->

<?php foreach ($produitsParCategorie as $categorie => $produitsCat):
    $idAncre = strtolower($categorie);
?>

    <section class="<?= $idAncre ?>" data-categorie="<?= $idAncre ?>">

        <h2 class="titre-section" id="<?= $idAncre ?>"><?= $categorie ?></h2>

        <div class="produits">
            <?php foreach ($produitsCat as $p): ?>
                <div class="produit" data-prix="<?= $p['prix'] ?>">
                    <a href="pageproduit/produits.php?nom=<?= $p['nom'] ?>">
                        <img src="images/<?= $p['image'] ?>" alt="<?= $p['titre'] ?>">
                    </a>

                    <div class="produit-info">
                        <div class="panier-menu">
                            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">
                            <div class="panier-bulle">
                                <form method="POST">
                                    <input type="hidden" name="nom"     value="<?= $p['nom'] ?>">
                                    <input type="hidden" name="produit" value="<?= $p['titre'] ?>">
                                    <input type="hidden" name="prix"    value="<?= number_format($p['prix'], 2) ?>">

                                    <label>Quantité :</label>
                                    <input type="number" name="quantite" value="1" min="1" max="20">

                                    <label>Saveur :</label>
                                    <select name="saveur">
                                        <?php foreach ($p['saveurs'] as $saveur): ?>
                                            <option value="<?= $saveur ?>"><?= $saveur ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <button type="submit" name="ajouter_panier">Ajouter</button>
                                </form>
                            </div>
                        </div>

                        <div>
                            <h3><?= $p['titre'] ?></h3>
                            <p class="prix"><?= number_format($p['prix'], 2, ',', ' ') ?>€</p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </section>

<?php endforeach; ?>


<!-- ============================================================
     JAVASCRIPT : tri par prix + filtre par categorie
============================================================ -->
<script>
const selectTri       = document.getElementById('filtre-tri');
const selectCategorie = document.getElementById('filtre-categorie');

// Sauvegarder l'ordre original des produits dans chaque section
const ordreOriginal = {};
document.querySelectorAll('section[data-categorie]').forEach(function(section) {
    const cat = section.dataset.categorie;
    const conteneur = section.querySelector('.produits');
    ordreOriginal[cat] = Array.from(conteneur.children);
});

function appliquerFiltres() {
    const tri       = selectTri.value;
    const catChoix  = selectCategorie.value;

    document.querySelectorAll('section[data-categorie]').forEach(function(section) {
        const cat = section.dataset.categorie;

        // Filtre categorie : montrer / cacher la section entiere
        if (catChoix !== 'toutes' && catChoix !== cat) {
            section.style.display = 'none';
            return;
        }
        section.style.display = '';

        // Tri par prix au sein de la section
        const conteneur = section.querySelector('.produits');
        let produits;

        if (tri === 'defaut') {
            produits = ordreOriginal[cat].slice();
        } else {
            produits = Array.from(conteneur.children).sort(function(a, b) {
                const prixA = parseFloat(a.dataset.prix);
                const prixB = parseFloat(b.dataset.prix);
                return tri === 'croissant' ? prixA - prixB : prixB - prixA;
            });
        }

        // Reinjecter dans le bon ordre
        produits.forEach(p => conteneur.appendChild(p));
    });
}

selectTri.addEventListener('change', appliquerFiltres);
selectCategorie.addEventListener('change', appliquerFiltres);
</script>


<footer>
    <p>suivez nous sur nos réseaux!<br>
        <img src="images/Iconinstagram.jpg" class="icon">
        <img src="images/Icontiktok.jpg" class="icon">
        <img src="images/Icontwitter.png" class="icon">
    </p>
    <div class="infos-footer">
        <div class="info">
            <img src="images/Iconlocalisation.png" class="icon">
            <span>5 avenue de la république, 75015 Paris</span>
        </div>
        <div class="info">
            <img src="images/Iconhorloge.png" class="icon">
            <span>Tous les jours 9h - 20h</span>
        </div>
    </div>
    <h5>© 2026 Pâtisserie</h5>
</footer>

<!-- Bouton mode sombre -->
<button id="btn-darkmode" class="btn-darkmode">☾</button>
<script src="darkmode.js"></script>

</body>
</html>
