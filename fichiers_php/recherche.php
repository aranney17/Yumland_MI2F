<?php
session_start();

/* SECURITE : meme logique que presentation.php */
if (isset($_SESSION['id'])) {
    $usersCheck = json_decode(file_get_contents("../data/infoclient.json"), true) ?? [];
    foreach ($usersCheck as $u) {
        if ($u['id'] == $_SESSION['id']) {
            if ($u['bloque'] ?? false) {
                session_destroy();
                die("Votre compte a été bloqué. <a href='../fichiers_php/connexion.php'>Retour</a>");
            }
            if ($u['role'] !== 'client') {
                $redir = [
                    'cuisinier'      => '../fichiers_php/commandes.php',
                    'livreur'        => '../fichiers_php/livraison.php',
                    'administrateur' => '../fichiers_php/administrateur.php'
                ];
                header("Location: " . ($redir[$u['role']] ?? '../fichiers_php/accueil.php'));
                exit();
            }
            break;
        }
    }
}

$produits = json_decode(file_get_contents('../data/produits.json'), true) ?? [];

$q = trim($_GET['q'] ?? '');

/* -------------------------------------------------------------
   Normalisation SANS mbstring (corrige l'erreur 500).
   strtolower gere l'ASCII (A-Z). La table strtr gere les accents,
   minuscules ET majuscules, pour une recherche tolerante.
------------------------------------------------------------- */
function normaliser($s) {
    $s = strtolower($s);
    $s = strtr($s, [
        'à'=>'a','â'=>'a','ä'=>'a','á'=>'a','ã'=>'a',
        'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
        'î'=>'i','ï'=>'i','í'=>'i',
        'ô'=>'o','ö'=>'o','ó'=>'o','õ'=>'o',
        'û'=>'u','ü'=>'u','ù'=>'u','ú'=>'u',
        'ç'=>'c','ñ'=>'n',
        'À'=>'a','Â'=>'a','Ä'=>'a','Á'=>'a',
        'É'=>'e','È'=>'e','Ê'=>'e','Ë'=>'e',
        'Î'=>'i','Ï'=>'i','Í'=>'i',
        'Ô'=>'o','Ö'=>'o','Ó'=>'o',
        'Û'=>'u','Ü'=>'u','Ù'=>'u','Ú'=>'u',
        'Ç'=>'c','Ñ'=>'n'
    ]);
    return $s;
}

/* Filtrage : titre, nom ou categorie contient la recherche */
$resultats = [];
if ($q !== '') {
    $qn = normaliser($q);
    foreach ($produits as $p) {
        $foin = normaliser(($p['titre'] ?? '') . ' ' . ($p['nom'] ?? '') . ' ' . ($p['categorie'] ?? ''));
        if (strpos($foin, $qn) !== false) {
            $resultats[] = $p;
        }
    }
}

/* MODE AJAX (autocompletion) : JSON, max 8 suggestions */
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $sortie = [];
    foreach (array_slice($resultats, 0, 8) as $p) {
        $sortie[] = [
            'nom'   => $p['nom'],
            'titre' => $p['titre'],
            'prix'  => $p['prix'],
            'image' => $p['image']
        ];
    }
    echo json_encode($sortie);
    exit;
}

/* Un seul resultat : direction la page produit */
if ($q !== '' && count($resultats) === 1) {
    header("Location: produits.php?nom=" . urlencode($resultats[0]['nom']));
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche : <?= htmlspecialchars($q) ?></title>
    <link rel="stylesheet" href="../fichiers_css/couleurs.css">
    <link rel="stylesheet" href="../fichiers_css/structg.css">
    <link rel="stylesheet" href="../fichiers_css/cssaccueil.css">
    <link rel="stylesheet" href="../fichiers_css/darkmode.css">
    <style>
        .resultats-titre { max-width: 1000px; margin: 30px auto 10px; padding: 0 20px; }
        .aucun-resultat  { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
    </style>
</head>
<body>

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

<form class="search-bar" action="recherche.php" method="get" autocomplete="off">
    <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder=" qu'est-ce qui vous ferait plaisir?">
    <button type="submit"><img src="../images/Iconloupe.png" alt="loupe"></button>
</form>
<br>

<nav class="menu-horizontal">
    <ul>
        <li><a href="../fichiers_php/menu.php">Menus</a></li>
        <li><a href="../fichiers_php/presentation.php">Tous nos produits</a></li>
    </ul>
</nav>

<h2 class="resultats-titre">
    <?php if ($q === ''): ?>
        Tapez un nom de produit dans la barre de recherche.
    <?php else: ?>
        Résultats pour « <?= htmlspecialchars($q) ?> » : <?= count($resultats) ?> produit(s)
    <?php endif; ?>
</h2>

<?php if ($q !== '' && count($resultats) === 0): ?>
    <p class="aucun-resultat">Aucun produit ne correspond à votre recherche. Essayez un autre mot (ex : « tarte », « matcha », « chocolat »).</p>
<?php endif; ?>

<?php if (count($resultats) > 0): ?>
    <section>
        <div class="produits">
            <?php foreach ($resultats as $p): ?>
                <div class="produit" data-prix="<?= $p['prix'] ?>">
                    <a href="produits.php?nom=<?= urlencode($p['nom']) ?>">
                        <img src="../images/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['titre']) ?>">
                    </a>
                    <div class="produit-info">
                        <div>
                            <h3><?= htmlspecialchars($p['titre']) ?></h3>
                            <p class="prix"><?= number_format($p['prix'], 2, ',', ' ') ?>€</p>
                            <a href="produits.php?nom=<?= urlencode($p['nom']) ?>">Voir le produit</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

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