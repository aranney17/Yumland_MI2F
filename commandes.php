<?php
    session_start();

    // Vérifier connexion
    if (!isset($_SESSION['id'])) {
        header("Location: connexion.php");
        exit();
    }

    // Charger JSON
    $json = file_get_contents("data/commande.json");
    $commandes = json_decode($json, true);

    // Terminer
    if (isset($_GET['terminer'])) {

        $idCommande = $_GET['terminer'];

        foreach ($commandes as &$commande) {
            if ($commande['reference'] == $idCommande) {

                // changer statut
                $commande['statut'] = "commande préparée";
                break;
            }
        }
    }

    // sauvegarde
    file_put_contents("data/commande.json", json_encode($commandes, JSON_PRETTY_PRINT));

    // Date du jour 
    $ajd = date("Y-m-d");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="commandes.css">
    <link rel="stylesheet" href="structg.css">
    <link rel="stylesheet" href="couleurs.css">
    <title>Commandes</title>
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

            <!-- PROFIL -->
            <div class="profil-menu">
                <img src="images/Iconprofil.png" alt="Profil" class="icon">
                <div class="profil-bulle">
                    <a href="profil.php">Profil</a>
                    <a href="logout.php">Déconnexion</a>
                </div>
            </div>
        </div>
    </header>
  
    <main>
        <h2>Commandes</h2>
        <div class="commandes">

        <?php foreach ($commandes as $commande): ?>

            <?php
            // recherche des commandes a preparer
            if ($commande['statut'] == "a preparer" && $commande['datelivraison'] == $ajd):
            ?>


                <div class="commande-case">
                    <div class="commande-header">
                        <span class="numero"><?= $commande['reference'] ?></span>
                        <span class="prix"><?= $commande['montant'] ?>€</span>
                    </div>

                    <div class="commande-details">
                        <?php foreach ($commande['produits'] as $produit): ?>
                            <?= $produit['nom'] ?> x<?= $produit['quantite'] ?><br>
                        <?php endforeach; ?>
                    </div>
                    <a href="commandes.php?terminer=<?= $commande['reference'] ?>" class="btn"> Terminer </a>
                </div>
            <?php endif; ?>

        <?php endforeach; ?>
                

        </div>
    </main>

    <footer>
        <p>suivez nous sur nos réseaux!
            </br>
                <img src="images/Iconinstagram.jpg" alt="instagram" class="icon">
                <img src="images/Icontiktok.jpg" alt="tiktok" class="icon">
                <img src="images/Icontwitter.png" alt="twitter" class="icon">
        </p>

        <div class="infos-footer">
            <div class="info">
                <img src="images/Iconlocalisation.png" alt="maps" class="icon">
                <span>5 avenue de la république, 75015 Paris</span>
            </div>

            <div class="info">
                <img src="images/Iconhorloge.png" alt="horloge" class="icon">
                <span>Tous les jours 9h - 20h</span>
            </div>
        </div>
        <h5>© 2026 Pâtisserie</h5>
    </footer>
  
</body>
</html>
