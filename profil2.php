<?php
session_start();
// Vérifier si utilisateur connecté
if (!isset($_SESSION['id'])) {
    header("Location: connexion.php");
    exit();
}

// Charger l'utilisateur connecté
$json = file_get_contents("infoclient.json");
$users = json_decode($json, true);
$id = $_SESSION['id'];
$userTrouve = null;
foreach ($users as $user) {
    if ($user['id'] == $id) {
        $userTrouve = $user;
        break;
    }
}

// Charger les commandes et filtrer celles de l'utilisateur connecté
$commandesJson = file_get_contents("commandes.json");
$toutesCommandes = json_decode($commandesJson, true);
$mesCommandes = [];

if (is_array($toutesCommandes)) {
    foreach ($toutesCommandes as $commande) {
        // Recherche par nom + prénom (insensible à la casse)
        if (
            strtolower($commande['nom']) == strtolower($userTrouve['nom']) &&
            strtolower($commande['prenom']) == strtolower($userTrouve['prenom'])
        ) {
            $mesCommandes[] = $commande;
        }
    }
}

// Trier par date décroissante (plus récent en premier)
usort($mesCommandes, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique de commandes</title>
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
        <h1><a href="accueil.php" class="logo">La Cour des délices</a></h1>
        <div class="top-icons">
            <div class="profil-menu">
                <img src="images/Iconprofil.png" alt="Profil" class="icon">
                <div class="profil-bulle">
                    <a href="profil.php">Profil</a>
                    <a href="deconnexion.php">Déconnexion</a>
                </div>
            </div>
            <a href="panier.php">
                <img src="images/Iconpanier.png" alt="Panier" class="icon" id="panier">
            </a>
        </div>
    </header>

    <main class="container">
        <aside class="sidebar">
            <ul class="menu">
                <li><a href="profil.php">Informations</a></li>
                <li><a href="profil2.php"><strong>Historique de commandes</strong></a></li>
                <li>Données personnelles</li>
            </ul>
            <br>
            <a href="deconnexion.php"><p class="logout">Déconnexion</p></a>
        </aside>

        <section>
            <h2>Mes commandes</h2>

            <?php if (empty($mesCommandes)) : ?>
                <p class="aucune-commande">Vous n'avez pas encore passé de commande.</p>
            <?php else : ?>
                <table class="table-commandes">
                    <tr>
                        <th>Référence</th>
                        <th>Date</th>
                        <th>Livraison</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Détails</th>
                    </tr>

                    <?php foreach ($mesCommandes as $commande) : ?>

                        <?php
                            // Classe CSS selon le statut
                            $statutClass = "statut-preparer";
                            if ($commande['statut'] == "livre") $statutClass = "statut-livre";
                            if ($commande['statut'] == "annule") $statutClass = "statut-annule";

                            // Formatage des dates
                            $dateCommande = date("d/m/Y", strtotime($commande['date']));
                            $dateLivraison = date("d/m/Y", strtotime($commande['datelivraison']));
                        ?>

                        <!-- Ligne principale -->
                        <tr>
                            <td><?= htmlspecialchars($commande['reference']) ?></td>
                            <td><?= $dateCommande ?></td>
                            <td><?= $dateLivraison ?></td>
                            <td><?= number_format($commande['montant'], 2) ?> €</td>
                            <td>
                                <span class="statut <?= $statutClass ?>">
                                    <?= htmlspecialchars($commande['statut']) ?>
                                </span>
                            </td>
                            <td>
                                <button class="bouton-details">
                                    Voir
                                </button>
                            </td>
                        </tr>

                        <!-- Ligne détails (cachée par défaut) -->
                        <tr id="details-<?= $commande['reference'] ?>">
                            <td colspan="6">
                                <table class="table-details">
                                    <tr>
                                        <th>Produit</th>
                                        <th>Saveur</th>
                                        <th>Quantité</th>
                                        <th>Prix unitaire</th>
                                        <th>Sous-total</th>
                                    </tr>
                                    <?php foreach ($commande['produits'] as $produit) : ?>
                                        <tr>
                                            <td><?= htmlspecialchars($produit['nom']) ?></td>
                                            <td><?= isset($produit['saveur']) ? htmlspecialchars($produit['saveur']) : '—' ?></td>
                                            <td><?= $produit['quantite'] ?></td>
                                            <td><?= number_format($produit['prix'], 2) ?> €</td>
                                            <td><?= number_format($produit['prix'] * $produit['quantite'], 2) ?> €</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                                <p><strong>Adresse de livraison :</strong> <?= htmlspecialchars($commande['adresse']) ?></p>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>suivez nous sur nos réseaux!
            <br>
            <img src="images/Iconinstagram.jpg" alt="instagram" class="icon">
            <img src="images/Icontiktok.jpg" alt="tiktok" class="icon">
            <img src="images/Icontwitter.png" alt="twitter" class="icon">
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
