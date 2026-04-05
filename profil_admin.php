<?php
//Charger les utilisateurs depuis le JSON
$json = file_get_contents("data/infoclient.json");
$utilisateurs = json_decode($json, true);

// Récupérer l'ID passé dans l'URL
$id = $_GET['id'] ?? null; // si aucun id, null

// Chercher l'utilisateur correspondant
$userTrouve = null;
if ($id !== null) {
    foreach ($utilisateurs as $user) {
        if ($user['id'] == $id) {
            $userTrouve = $user;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin</title>
    <link rel="stylesheet" href="couleurs.css">
    <link rel="stylesheet" href="structg.css">
    <link rel="stylesheet" href="profil.css">
</head>
<body>
<header>
    <div class="barres">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <h1><a href="accueil.html" class="logo">Pâtisserie</a></h1>

    <div class="top-icons">
        <div class="profil-menu">
            <img src="images/Iconprofil.png" alt="Profil" class="icon">
            <div class="profil-bulle">
                <a href="profil_admin.php?id=<?= $id ?>">Profil</a>
                <a href="accueil.html">Déconnexion</a>
            </div>
        </div>
        <a href=""><img src="images/Iconpanier.png" alt="Panier" class="icon" id="panier"></a>
    </div>
</header>

<main class="container">
<aside class="sidebar">
    <ul class="menu">
        <li><a href="profil_admin.php?id=<?= $id ?>">Informations</a></li>
        <li><a href="profil2_admin.php?id=<?= $id ?>">Historique de commandes</a></li>
        <li>Données personnelles</li>
    </ul>
    <br>
</aside>

<section class="informations">
    <?php if ($userTrouve): ?>
        <h2 class="title">
            Informations  
            <!-- Ici le crayon sert à modifier -->
            <a href="modifier_admin.php?id=<?= $id ?>"><img src="images/crayon.png" alt="crayon" width="15" height="15" /></a>
        </h2>

        <div class="block">Civilité :
            <br><?= $userTrouve['civilite'] ?? 'N/A' ?>
        </div>
        <span class="inline">Nom :
            <br><?= $userTrouve['nom'] ?? 'N/A' ?>
        </span>
        <span class="inline">Prénom :
            <br><?= $userTrouve['prenom'] ?? 'N/A' ?>
        </span>
        <div class="block">Date de naissance :
            <br><?= $userTrouve['date_naissance'] ?? 'N/A' ?>
        </div>
        <div class="block">Téléphone :
            <br><?= $userTrouve['telephone'] ?? 'N/A' ?>
        </div>
        <div class="block">Code postal :
            <br><?= $userTrouve['code_postal'] ?? 'N/A' ?>
        </div>
        <div class="block">Adresse :
            <br><?= $userTrouve['adresse'] ?? 'N/A' ?>
        </div>
        <div class="block">Adresse mail :
            <br><?= $userTrouve['email'] ?? 'N/A' ?>
        </div>
        <div class="block">Mot de passe :
            <br>********* <!-- On ne montre jamais le mot de passe -->
        </div>

        <div class="block">Statut :
            <br><?= $userTrouve['statut'] ?? 'N/A' ?>
        </div>

        <div class="block">Remise :
            <br><?= $userTrouve['remise'] ?? 0 ?>%
        </div>

        <div class="block">Compte bloqué :
            <br><?= ($userTrouve['bloque'] ?? false) ? 'Oui' : 'Non' ?>
        </div>

    <?php else: ?>
        <p>Utilisateur introuvable</p>
    <?php endif; ?>
</section>
</main>

<footer>
    <p>Suivez nous sur nos réseaux!
        <br>
        <img src="images/Iconinstagram.jpg" alt="instagram" class="icon">
        <img src="images/Icontiktok.jpg" alt="tiktok" class="icon">
        <img src="images/Iconinstagram.jpg" alt="instagram" class="icon">
    </p>
    <div class="infos-footer">
        <div class="info">
            <img src="images/Iconlocalisation.png" alt="maps" class="icon">
            <span>5 av de la république, 75300 Paris</span>
        </div>
        <div class="info">
            <img src="images/Iconhorloge.png" alt="horloge" class="icon">
            <span>Tous les jours 9h - 22h</span>
        </div>
    </div>
    <h5>© 2026 Pâtisserie</h5>    
</footer>
</body>
</html>
