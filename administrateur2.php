<?php
session_start();

// Vérifier connexion
if (!isset($_SESSION['id'])) {
    header("Location: connexion.php");
    exit();
}
if($_SESSION["role"] != "administrateur"){
    header("Location: connexion.php");
}
$json = file_get_contents("data/commande.json");
$commandes = json_decode($json, true);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administrateur</title>
    <link rel="stylesheet" href="structg.css">
    <link rel="stylesheet" href="couleurs.css">
    <link rel="stylesheet" href="administrateur2.css">
</head>
<body>

<header>
        <div class="barres">
            <span></span>
            <span></span>
            <span></span>
        </div> 

        <h1><a href="accueil.php" class="logo">La Cour des Délices</a></h1>
        <div class="top-icons">
            <a href="profil.php"> <img src="images/Iconprofil.png" alt="Profil" class="icon"> </a>
            <a href="logout.php"><p class="deconnexion">déconnexion</p></a>
        </div>
    </header>

    <div class="search-bar">
        <input type="search" placeholder="Chercher un utilisateur">
        <button><img src="images/Iconloupe.png" alt="loupe"></button>
    </div>

    <nav class="menu-horizontal">
    <ul>
        <li>
            <a href="administrateur.php">Utilisateurs</a>
        </li>

        <li>
            <a href="administrateur2.php" class="active">Commandes</a>
        </li>
    </ul>
</nav>

<main>
    <button class="filtre">
        Filtrer  <img src="images/filter.png">
    </button>
<section>

<table>
<tr>
    <th>Date</th>
    <th>Référence</th>
    <th>Client</th>
    <th>Montant</th>
    <th>État du paiement</th>
    <th>Statut de la commande</th>
    <th>Détails</th>
</tr>

<?php foreach ($commandes as $commande): ?>
<tr class="ligne">

    <td><?= date("d M Y", strtotime($commande['date'])) ?></td>

    <td><?= $commande['reference'] ?></td>

    <td><?= $commande['prenom'] ?></td>

    <td><?= number_format($commande['montant'], 2, ',', ' ') ?> €</td>

    <!-- Paiement -->
    <td>
        <span class="case payee">
            <?= strtoupper($commande['paiement']) ?>
        </span>
    </td>

    <!-- Statut -->
    <td>
        <span class="case <?= $commande['statut'] ?>">
            <?= strtoupper($commande['statut']) ?>
        </span>
    </td>

    <!-- Détails -->
     <td>
        <button class="bouton-details">
            <img src="images/iconfleche.jpg" alt="Détails" class="fleche">
        </button>
    </td>
</tr>

<tr id="details-<?= $commande['id'] ?>" >
    <td colspan="7">

        <table class="table-details">
            <tr>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Prix</th>
            </tr>

            <?php foreach ($commande['produits'] as $produit): ?>
            <tr>
                <td><?= $produit['produit'] ?></td>
                <td><?= $produit['quantite'] ?></td>
                <td><?= $produit['prix'] ?> €</td>
            </tr>
            <?php endforeach; ?>

        </table>

    </td>
</tr>

<?php endforeach; ?>

</table>

</section>
</main>

<footer>
        <p>suivez nous sur nos réseaux!
            </br>
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



