<?php
session_start();

// Vérifier si utilisateur connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.html");
    exit();
}

// Charger JSON
$json = file_get_contents("infoclient.json");
$users = json_decode($json, true);

// Trouver l'utilisateur connecté
$id = $_SESSION['user_id'];
$userTrouve = null;

foreach ($users as $user) {
    if ($user['id'] == $id) {
        $userTrouve = $user;
        break;
    }
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
</head>
<body>

<header>
    <h1><a href="accueil.php" class="logo">Pâtisserie</a></h1>

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
        <li><a href="profil.php">Informations</a></li>
        <li>Adresses</li>
        <li><a href="profil2.php">Historique de commandes</a></li>
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
    <br><?= $userTrouve['civilite'] ?? '' ?>
</div>

<span class="inline">Nom :
    <br><?= $userTrouve['nom'] ?>
</span>

<span class="inline">Prénom :
    <br><?= $userTrouve['prenom'] ?>
</span>

<div class="block">Date de naissance :
    <br><?= $userTrouve['date_naissance'] ?? '' ?>
</div>

<div class="block">Téléphone :
    <br><?= $userTrouve['telephone'] ?? '' ?>
</div>

<div class="block">Code postal :
    <br><?= $userTrouve['code_postal'] ?? '' ?>
</div>

<div class="block">Adresse :
    <br><?= $userTrouve['adresse'] ?? '' ?>
</div>

<div class="block">Adresse mail :
    <br><?= $userTrouve['email'] ?>
</div>

<div class="block">Mot de passe :
    <br>*********
</div>

<?php else: ?>
<p>Utilisateur introuvable</p>
<?php endif; ?>

</section>
</main>

</body>
</html>
