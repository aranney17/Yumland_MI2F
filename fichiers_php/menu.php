<?php
session_start();

/* 
  SECURITE : reste connecte et check bloque/role.
*/
if (isset($_SESSION['id'])) {
    $usersCheck = json_decode(file_get_contents("../data/infoclient.json"), true) ?? [];
    foreach ($usersCheck as $u) {
        if ($u['id'] == $_SESSION['id']) {
            if ($u['bloque'] ?? false) {
                session_destroy();
                die("Votre compte a été bloqué. <a href='../fichiers_php/connexion.php'>Retour</a>");
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

/* Lecture des produits */
$produits = json_decode(file_get_contents('../data/produits.json'), true) ?? [];

/* definition des menus */
$MENUS = [
    [
        'id' => 'matcha', 'titre' => 'Menu Matcha Addict', 'prix' => 9.50,
        'badge' => 'Nouveau',
        'description' => ['1 boisson au matcha', '1 pâtisserie au matcha'],
        'slots' => [
            ['cle'=>'boisson',    'label'=>'Boisson au matcha',    'categories'=>['Boissons'],                       'matcha'=>true],
            ['cle'=>'patisserie', 'label'=>'Pâtisserie au matcha', 'categories'=>['Patisseries','Gateaux','Tartes'], 'matcha'=>true],
        ],
    ],
    [
        'id' => 'petitdej', 'titre' => 'Menu Petit Déjeuner', 'prix' => 5.50,
        'description' => ['1 boisson au choix', '1 viennoiserie au choix'],
        'slots' => [
            ['cle'=>'boisson',      'label'=>'Boisson',      'categories'=>['Boissons'],      'matcha'=>false],
            ['cle'=>'viennoiserie', 'label'=>'Viennoiserie', 'categories'=>['Viennoiseries'], 'matcha'=>false],
        ],
    ],
    [
        'id' => 'gourmand', 'titre' => 'Menu Gourmand', 'prix' => 10.50,
        'description' => ['1 boisson au choix', '1 gourmandise ou une part de gâteau'],
        'slots' => [
            ['cle'=>'boisson',     'label'=>'Boisson',              'categories'=>['Boissons'],                'matcha'=>false],
            ['cle'=>'gourmandise', 'label'=>'Gourmandise ou gâteau','categories'=>['Gourmandises','Gateaux'],  'matcha'=>false],
        ],
    ],
    [
        'id' => 'signature', 'titre' => 'Menu Signature', 'prix' => 7.50,
        'description' => ['1 boisson au choix', '1 pâtisserie ou 1 part de tarte'],
        'slots' => [
            ['cle'=>'boisson',    'label'=>'Boisson',            'categories'=>['Boissons'],             'matcha'=>false],
            ['cle'=>'patisserie', 'label'=>'Pâtisserie ou tarte','categories'=>['Patisseries','Tartes'], 'matcha'=>false],
        ],
    ],
];

/* Filtre les produits eligibles pour un slot donne */
function produitsEligibles($produits, $slot) {
    $res = [];
    foreach ($produits as $p) {
        if (!in_array($p['categorie'], $slot['categories'], true)) continue;
        if (!empty($slot['matcha'])) {
            $hay = strtolower($p['nom'] . ' ' . $p['titre']);
            if (strpos($hay, 'matcha') === false) continue;
        }
        $res[] = $p;
    }
    return $res;
}

/* ajout d'un menu au panier */
if (isset($_POST['ajouter_menu'])) {
    $menuId = $_POST['menu_id'] ?? '';

    $menuDef = null;
    foreach ($MENUS as $m) {
        if ($m['id'] === $menuId) { $menuDef = $m; break; }
    }

    if ($menuDef) {
        // Construire la composition a partir des choix
        $composition = [];
        foreach ($menuDef['slots'] as $slot) {
            $choix = trim($_POST['choix_' . $slot['cle']] ?? '');
            if ($choix === '') $choix = '(non choisi)';
            $composition[$slot['label']] = $choix;
        }

        $panierFile = '../data/panier.json';
        $panier = file_exists($panierFile) ? (json_decode(file_get_contents($panierFile), true) ?? []) : [];

        $panier[] = [
            "type"        => "menu",
            "nom_menu"    => $menuDef['titre'],
            "produit"     => $menuDef['titre'],  // compat avec le code qui lit ['produit']
            "prix"        => $menuDef['prix'],
            "saveur"      => "",                  // compat
            "quantite"    => 1,
            "composition" => $composition
        ];

        file_put_contents($panierFile, json_encode($panier, JSON_PRETTY_PRINT));
        $_SESSION['flash_menu'] = $menuDef['titre'] . " ajouté au panier !";
        header("Location: ../fichiers_php/menu.php");
        exit();
    }
}

$flash = $_SESSION['flash_menu'] ?? null;
unset($_SESSION['flash_menu']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos menus</title>
    <link rel="icon" href="">
    <link rel="stylesheet" href="../fichiers_css/couleurs.css">
    <link rel="stylesheet" href="../fichiers_css/structg.css">
    <link rel="stylesheet" href="../fichiers_css/menu.css">
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
<br>

<nav class="menu-horizontal">
    <ul>
        <li><a href="../fichiers_php/menu.php" class="active">Menus</a></li>
        <li><a href="../fichiers_php/presentation.php">Tous nos produits</a></li>
    </ul>
</nav>

<?php if ($flash): ?>
    <div class="flash-menu"><?= htmlspecialchars($flash) ?> <a href="../fichiers_php/panier.php">Voir le panier</a></div>
<?php endif; ?>

<section class="menu-slider">
    <button class="arrow left" type="button" aria-label="Précédent">&#10094;</button>

    <div class="slider-container" id="slider">
        <?php foreach ($MENUS as $menu): ?>
            <div class="menu-card">
                <?php if (!empty($menu['badge'])): ?>
                    <span class="menu-badge"><?= htmlspecialchars($menu['badge']) ?></span>
                <?php endif; ?>

                <h3 class="menu-title"><?= htmlspecialchars($menu['titre']) ?></h3>

                <div class="menu-info">
                    <p class="menu-description">
                        <?php foreach ($menu['description'] as $ligne): ?>
                            <?= htmlspecialchars($ligne) ?><br>
                        <?php endforeach; ?>
                    </p>
                    <p class="menu-price"><?= number_format($menu['prix'], 2, ',', ' ') ?>€</p>
                </div>

                <button type="button" class="menu-button"
                        onclick="ouvrirModal('modal-<?= $menu['id'] ?>')">
                    Commander
                </button>
            </div>
        <?php endforeach; ?>
    </div>

    <button class="arrow right" type="button" aria-label="Suivant">&#10095;</button>
</section>


<!--  choix boisson + accompagnement -->
<?php foreach ($MENUS as $menu): ?>
    <div class="menu-modal" id="modal-<?= $menu['id'] ?>">
        <div class="menu-modal-contenu">
            <button type="button" class="fermer" onclick="fermerModal('modal-<?= $menu['id'] ?>')" aria-label="Fermer">&times;</button>
            <h3><?= htmlspecialchars($menu['titre'] ) ?></h3>
            <p class="prix-modal"><?= number_format($menu['prix'], 2, ',', ' ') ?>€</p>

            <form method="POST">
                <input type="hidden" name="menu_id" value="<?= $menu['id'] ?>">

                <?php foreach ($menu['slots'] as $slot):
                    $options = produitsEligibles($produits, $slot); ?>

                    <label for="<?= $menu['id'] ?>_<?= $slot['cle'] ?>"><?= htmlspecialchars($slot['label']) ?> :</label>

                    <?php if (count($options) === 0): ?>
                        <p class="slot-vide">Aucun produit disponible pour ce choix.</p>
                    <?php else: ?>
                        <select name="choix_<?= $slot['cle'] ?>" id="<?= $menu['id'] ?>_<?= $slot['cle'] ?>" required>
                            <?php foreach ($options as $opt): ?>
                                <option value="<?= htmlspecialchars($opt['titre']) ?>">
                                    <?= htmlspecialchars($opt['titre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                <?php endforeach; ?>

                <button type="submit" name="ajouter_menu" class="valider">
                    Valider et ajouter au panier
                </button>
            </form>
        </div>
    </div>
<?php endforeach; ?>


<footer>
    <p>suivez nous sur nos réseaux!<br>
        <img src="../images/Iconinstagram.jpg" alt="instagram" class="icon">
        <img src="../images/Icontiktok.jpg" alt="tiktok" class="icon">
        <img src="../images/Icontwitter.png" alt="twitter" class="icon">
    </p>
    <div class="infos-footer">
        <div class="info"><img src="../images/Iconlocalisation.png" alt="maps" class="icon"><span>5 avenue de la république, 75015 Paris</span></div>
        <div class="info"><img src="../images/Iconhorloge.png" alt="horloge" class="icon"><span>Tous les jours 9h - 20h</span></div>
    </div>
    <h5>© 2026 Pâtisserie</h5>
</footer>

<script>
/* Carrousel  */
const slider = document.getElementById('slider');
document.querySelector('.arrow.left').addEventListener('click', function() {
    slider.scrollBy({ left: -320, behavior: 'smooth' });
});
document.querySelector('.arrow.right').addEventListener('click', function() {
    slider.scrollBy({ left: 320, behavior: 'smooth' });
});

/* Modals */
function ouvrirModal(id) {
    document.getElementById(id).classList.add('ouvert');
}
function fermerModal(id) {
    document.getElementById(id).classList.remove('ouvert');
}
/* Fermer en cliquant sur le fond sombre */
document.querySelectorAll('.menu-modal').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === modal) modal.classList.remove('ouvert');
    });
});
</script>

<button id="btn-darkmode" class="btn-darkmode">☾</button>
<script src="../fichiers_js/darkmode.js"></script>
<script src="../fichiers_js/recherche.js"></script>
</body>
</html>
