<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: connexion.php");
    exit();
}

$users = json_decode(file_get_contents("data/infoclient.json"), true) ?? [];
$id = $_SESSION['id'];
$userTrouve = null;
foreach ($users as $user) {
    if ($user['id'] == $id) { $userTrouve = $user; break; }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil</title>
    <link rel="stylesheet" href="couleurs.css">
    <link rel="stylesheet" href="structg.css">
    <link rel="stylesheet" href="profil.css">
    <link rel="stylesheet" href="darkmode.css">
</head>
<body>

<header>
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
    </div>
</header>

<main class="container">

<aside class="sidebar">
    <ul class="menu">
        <li><a href="profil.php"><strong>Informations</strong></a></li>
        <li><a href="profil2.php">Historique de commandes</a></li>
        <li>Données personnelles</li>
    </ul>
    <br>
    <a href="logout.php"><p class="logout">Déconnexion</p></a>
</aside>

<section class="informations">

<?php if ($userTrouve): ?>

<h2 class="title">
    Informations
    <a href="modifier_profil.php"><img src="images/crayon.png" width="15"></a>
</h2>

<div class="block">Civilité :
    <br><?= htmlspecialchars($userTrouve['civilite'] ?? '') ?>
</div>

<span class="inline">Nom :
    <br><?= htmlspecialchars($userTrouve['nom']) ?>
</span>

<span class="inline">Prénom :
    <br><?= htmlspecialchars($userTrouve['prenom']) ?>
</span>

<div class="block">Date de naissance :
    <br><?= htmlspecialchars($userTrouve['date_naissance'] ?? '') ?>
</div>

<div class="block">Téléphone :
    <br><?= htmlspecialchars($userTrouve['telephone'] ?? '') ?>
</div>

<!-- AFFICHAGE ADRESSE SEPAREE -->
<div class="block">Rue :
    <br><?= htmlspecialchars($userTrouve['rue'] ?? '') ?>
</div>

<div class="block">Code postal :
    <br><?= htmlspecialchars($userTrouve['code_postal'] ?? '') ?>
</div>

<div class="block">Ville :
    <br><?= htmlspecialchars($userTrouve['ville'] ?? '') ?>
</div>

<div class="block">Adresse mail :
    <br><?= htmlspecialchars($userTrouve['mail']) ?>
</div>

<div class="block">Mot de passe :
    <br>*********
</div>

<?php else: ?>
<p>Utilisateur introuvable</p>
<?php endif; ?>

</section>
</main>

<footer>
    <p>Suivez nous sur nos réseaux!<br>
        <img src="images/Iconinstagram.jpg" class="icon">
        <img src="images/Icontiktok.jpg" class="icon">
        <img src="images/Icontwitter.png" class="icon">
    </p>
    <div class="infos-footer">
        <div class="info"><img src="images/Iconlocalisation.png" class="icon"><span>5 av de la république, 75300 Paris</span></div>
        <div class="info"><img src="images/Iconhorloge.png" class="icon"><span>Tous les jours 9h - 22h</span></div>
    </div>
    <h5>© 2026 Pâtisserie</h5>
</footer>

<button id="btn-darkmode" class="btn-darkmode">☾</button>
<script src="darkmode.js"></script>
</body>
</html>
