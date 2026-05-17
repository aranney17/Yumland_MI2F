<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: connexion.php");
    exit();
}

$utilisateurs = json_decode(file_get_contents("data/infoclient.json"), true) ?? [];

$roleConnecte = null;
$bloqueConnecte = false;
foreach ($utilisateurs as $u) {
    if ($u['id'] == $_SESSION['id']) {
        $roleConnecte = $u['role'];
        $bloqueConnecte = $u['bloque'] ?? false;
        break;
    }
}
if ($bloqueConnecte) {
    session_destroy();
    die("Votre compte a été bloqué.");
}
if ($roleConnecte !== 'administrateur') {
    http_response_code(403);
    die("Accès refusé. Cette page est réservée aux administrateurs.");
}

$id = $_GET['id'] ?? null;
$userTrouve = null;
if ($id !== null) {
    foreach ($utilisateurs as $user) {
        if ($user['id'] == $id) { $userTrouve = $user; break; }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil Admin</title>
    <link rel="stylesheet" href="couleurs.css">
    <link rel="stylesheet" href="structg.css">
    <link rel="stylesheet" href="profil.css">
    <link rel="stylesheet" href="darkmode.css">
</head>
<body>

<header>
    <div class="barres"><span></span><span></span><span></span></div>
    <h1><a href="administrateur.php" class="logo">La Cour des Délices</a></h1>
    <div class="top-icons">
        <div class="profil-menu">
            <img src="images/Iconprofil.png" class="icon">
            <div class="profil-bulle">
                <a href="profil.php">Mon profil</a>
                <a href="logout.php">Déconnexion</a>
            </div>
        </div>
    </div>
</header>

<main class="container">

<aside class="sidebar">
    <ul class="menu">
        <li><a href="administrateur.php">← Retour à la liste</a></li>
        <li><a href="profil_admin.php?id=<?= htmlspecialchars($id) ?>"><strong>Informations</strong></a></li>
    </ul>
</aside>

<section class="informations">
<?php if ($userTrouve): ?>
    <h2 class="title">
        Informations
        <a href="modifier_admin.php?id=<?= htmlspecialchars($id) ?>">
            <img src="images/crayon.png" alt="crayon" width="15">
        </a>
    </h2>

    <div class="block">Civilité :
        <br><?= htmlspecialchars($userTrouve['civilite'] ?? 'N/A') ?>
    </div>
    <span class="inline">Nom :
        <br><?= htmlspecialchars($userTrouve['nom'] ?? 'N/A') ?>
    </span>
    <span class="inline">Prénom :
        <br><?= htmlspecialchars($userTrouve['prenom'] ?? 'N/A') ?>
    </span>
    <div class="block">Date de naissance :
        <br><?= htmlspecialchars($userTrouve['date_naissance'] ?? 'N/A') ?>
    </div>
    <div class="block">Téléphone :
        <br><?= htmlspecialchars($userTrouve['telephone'] ?? 'N/A') ?>
    </div>
    <div class="block">Rue :
        <br><?= htmlspecialchars($userTrouve['rue'] ?? 'N/A') ?>
    </div>
    <div class="block">Code postal :
        <br><?= htmlspecialchars($userTrouve['code_postal'] ?? 'N/A') ?>
    </div>
    <div class="block">Ville :
        <br><?= htmlspecialchars($userTrouve['ville'] ?? 'N/A') ?>
    </div>
    <div class="block">Adresse mail :
        <br><?= htmlspecialchars($userTrouve['mail'] ?? 'N/A') ?>
    </div>
    <div class="block">Mot de passe :<br>*********</div>
    <div class="block">Rôle :
        <br><?= htmlspecialchars($userTrouve['role'] ?? 'N/A') ?>
    </div>
    <div class="block">Remise :
        <br><?= htmlspecialchars($userTrouve['remise'] ?? 0) ?>%
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
