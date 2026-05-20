<?php
session_start();
$userId = $_SESSION['id'] ?? null; //id de l'utilisateur connecté
// Check bloque + check role
if (isset($_SESSION['id'])) {
    $usersCheck = json_decode(file_get_contents("data/infoclient.json"), true) ?? [];
    foreach ($usersCheck as $u) {
        if ($u['id'] == $_SESSION['id']) {
            if ($u['bloque'] ?? false) {
                session_destroy();
                die("Votre compte a été bloqué. <a href='fichiers_php/connexion.php'>Retour</a>");
            }
            if ($u['role'] !== 'client') {
                $redir = [
                    'cuisinier'      => 'fichiers_php/commandes.php',
                    'livreur'        => 'fichiers_php/livraison.php',
                    'administrateur' => 'fichiers_php/administrateur.php'
                ];
                header("Location: " . ($redir[$u['role']] ?? 'fichiers_php/accueil.php'));
                exit();
            }
            break;
        }
    }
}
?>

<?php
 // Lire tous les produits
$fichierProduits = 'data/produits.json';
$produits = json_decode(file_get_contents($fichierProduits), true);
?>

<?php // Fonction pour récupérer un produit depuis le JSON
function getProduit($nom, $produits) {
    foreach ($produits as $p) {
        if ($p['nom'] === $nom) {
            return $p;
        }
    }
    return null;
} ?>

<!-- formatage de chaque produit pour les petits paniers -->
<?php

$produitMatchalatte = getProduit('Matchalatte', $produits);
$produitCappuccino = getProduit('Cappuccino', $produits);

$produitLattefraisematcha = getProduit('Lattefraisematcha', $produits);
$produitCookiegourmand = getProduit('Cookiegourmand', $produits);


$produitOperapistacheyuzu = getProduit('Opérapistacheyuzu', $produits);
$produitMacaron = getProduit('Macaron', $produits);
$produitParisbrest = getProduit('Parisbrest', $produits);
$produitTiramisu = getProduit('Tiramisu', $produits);

$produitBrownieube = getProduit('Brownieube', $produits);
$produitGateauaucitron = getProduit('Gateauaucitron', $produits);
$produitGateauauxfraises = getProduit('Gateauauxfraises', $produits);

$produitTarteauxpommes = getProduit('Tarteauxpommes', $produits);
$produitTartealaframboise = getProduit('Tarteàlaframboise', $produits);

?>

<?php
/* NOTIFICATION DE LIVRAISON */
$notifMessage = null;

if ($userId) {
    // Trouver les infos du client connecte
    $fichierClients = 'data/infoclient.json';
    $listeClients = json_decode(file_get_contents($fichierClients), true) ?? [];
    $clientConnecte = null;
    foreach ($listeClients as $c) {
        if ($c['id'] == $userId) {
            $clientConnecte = $c;
            break;
        }
    }

    if ($clientConnecte) {
        $fichierCommandes = 'data/commande.json';
        $listeCommandes = json_decode(file_get_contents($fichierCommandes), true) ?? [];
        $aModifier = false;

        foreach ($listeCommandes as &$cmd) {
            // On matche nom + prenom + telephone (faute d'id_client dans la commande)
            if ($cmd['nom']       === $clientConnecte['nom']
             && $cmd['prenom']    === $clientConnecte['prenom']
             && $cmd['telephone'] === $clientConnecte['telephone']
             && $cmd['statut']    === 'terminee'
             && empty($cmd['notif_vue'])) {

                $notifMessage = "Votre commande #" . $cmd['reference'] . " a été livrée !";
                $cmd['notif_vue'] = true;
                $aModifier = true;
                break; // on n'affiche qu'une notif a la fois
            }
        }
        unset($cmd);

        if ($aModifier) {
            file_put_contents($fichierCommandes, json_encode($listeCommandes, JSON_PRETTY_PRINT));
        }
    }
}
?>

<?php

if (isset($_POST['ajouter_panier'])) {

    $nom = $_POST['nom'];
    $produit = $_POST['produit'];
    $prix = $_POST['prix'];
    $saveur = $_POST['saveur'];
    $quantite = $_POST['quantite'];

    // Lire le panier
    $fichier = 'data/panier.json';

    if (file_exists($fichier)) {
        $panier = json_decode(file_get_contents($fichier), true);
    } else {
        $panier = [];
    }

    // Ajouter produit
    $panier[] = [
        "nom" => $nom,
        "produit" => $produit,
        "prix" => $prix,
        "saveur" => $saveur,
        "quantite" => $quantite
    ];

    // Sauvegarder
    file_put_contents($fichier, json_encode($panier, JSON_PRETTY_PRINT));

    // Recharge la page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pâtisserie</title>

    <link rel="icon" type="images/png" href="images/logosite.png">
    <link rel="stylesheet" href="fichiers_css/couleurs.css">
    <link rel="stylesheet" href="fichiers_css/structg.css">
    <link rel="stylesheet" href="fichiers_css/cssaccueil.css">
    <link rel="stylesheet" href="fichiers_css/darkmode.css">

</head>

<body>

<?php if ($notifMessage): ?>
    <div class="notif-livraison" id="notif-livraison">
        <span><?= htmlspecialchars($notifMessage) ?></span>
        <button onclick="document.getElementById('notif-livraison').style.display='none'">Fermer</button>
    </div>
<?php endif; ?>


    <div class="barres">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <h1> <a href="fichiers_php/panier.php?id=<?= urlencode($userId) ?>" class="logo">La Cour des Délices</a></h1>

<div class="top-icons">

    <div class="profil-menu">
        <img src="images/Iconprofil.png" alt="Profil" class="icon">

        <div class="profil-bulle">
    <?php if (isset($_SESSION["connecte"]) && $_SESSION["connecte"]): ?>
        <a href="fichiers_php/profil.php">Mon profil</a>
        <a href="fichiers_php/logout.php">Se déconnecter</a>
    <?php else : ?>
        <a href="fichiers_php/inscription.php">S'Inscrire</a>
        <a href="fichiers_php/connexion.php">Connexion</a>
    <?php endif; ?>
</div>
    </div>

    <a href="fichiers_php/panier.php">
        <img src="images/Iconpanier.png" alt="Panier" class="icon" id="panier">
    </a>

</div>

<div class="search-bar">
    <input type="search" placeholder=" qu'est-ce qui vous ferait plaisir?">
    <button><img src="images/Iconloupe.png" alt="loupe"></button>
</div>
    
<div class="categories">

    <a href="fichiers_php/presentation.php#viennoiseries" class="categorie">
        <img src="images/Croissantaubeurre.jpg" alt="viennoiserie">
        <p>Viennoiseries</p>
    </a>

    <a href="fichiers_php/presentation.php#boissons" class="categorie">
        <img src="images/Cafélatte.jpg" alt="Boissons">
        <p>Boissons</p>
    </a>

    <a href="fichiers_php/presentation.php#gourmandises" class="categorie">
        <img src="images/Cookiegourmand.jpg" alt="Gourmandises">
        <p>Gourmandises</p>
    </a>
    
    <a href="fichiers_php/presentation.php#patisseries" class="categorie">
        <img src="images/Eclairchocolat.jpg" alt="Patisseries">
        <p>Patisseries</p>
    </a>
    
    <a href="fichiers_php/presentation.php#gateaux" class="categorie">
        <img src="images/Gateauauxfraises.jpg" alt="Gateaux">
        <p>Gateaux</p>
    </a>
    
    <a href="fichiers_php/presentation.php#tartes" class="categorie">
        <img src="images/Tarteàlanoisette.jpg" alt="Tartes">
        <p>Tartes</p>
    </a>

</div>
    
<section class="hero">

    <div class="hero-content">
        <h2>Découvrez nos menus</h2>
        <a href="menu.html" class="btn-commande">Je commande</a>
    </div>

</section>
    
<section class="custom-cake">

    
    <div class="background-slider">
        <span></span>
        <span></span>
        <span></span>
    </div>

    
    <div class="custom-content">
        <h2>Gâteaux sur mesure</h2>
        <p>
            Un événement spécial ?
            Créez votre buffet, personnaliser votre piece montee
            et cree des souvenirs uniques.
        </p>

        <a href="commande-sur-mesure.html" class="btn-commande">
            Je commande
        </a>
    </div>

</section>


<section class="nouveautes">

    <h2 class="titre-section">Nouveautés</h2>

    <div class="produits">

        <div class="produit">
            <a href="fichiers_php/pageproduit/produits.php?nom=Opérapistacheyuzu">
            <img src="images/Opérapistacheyuzu.jpg" alt="Opera pistache yuzu">
            </a>
            
            <div class="produit-info">
                
                       <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitOperapistacheyuzu['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitOperapistacheyuzu['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitOperapistacheyuzu['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitOperapistacheyuzu['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>
                
                <div>
                    <h3>Opéra pistache & yuzu</h3>
                    <p class="prix">8,50€</p>
                </div>
            </div>
        </div>

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Lattefraisematcha">
            <img src="images/Lattefraisematcha.jpg" alt="latte matcha fraise">
            </a>
            
            <div class="produit-info">
                
                 <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitLattefraisematcha['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitLattefraisematcha['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitLattefraisematcha['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitLattefraisematcha['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

                <div>
                    <h3>Latte fraise & matcha</h3>
                    <p class="prix">6,50€</p>
                </div>              
            </div>
        </div>

    </div>

</section>
    
<section class="produit-highlight">
    <a href="pageproduit/produits.php?nom=Brownieube">
    <img src="images/Brownieube.jpg" alt="brownie Ube">
    </a>
    
    <div class="produit-info">
               <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitBrownieube['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitBrownieube['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitBrownieube['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitBrownieube['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

        <div>
          <h3>Brownie à l'Ube </h3>
           <p class="prix">4,90€</p>
            
        </div>          
      </div>

</section>
    
<section class="tendance">

    <h2 class="titre-section">Tendance</h2>

    <div class="produits">

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Matchalatte">
            <img src="images/Matchalatte.jpg" alt="matchalatte">
            </a>
            
            <div class="produit-info">
                       <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitMatchalatte['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitMatchalatte['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitMatchalatte['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitMatchalatte['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>
                <div>
                    <h3>Matcha latte</h3>
                    <p class="prix">5,50€</p>
                </div>
            </div>
        </div>

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Cookiegourmand">
            <img src="images/Cookiegourmand.jpg" alt="cookie gourmand">
            </a>
            <div class="produit-info">
                       <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitCookiegourmand['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitCookiegourmand['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitCookiegourmand['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitCookiegourmand['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>
                <div>
                    <h3>Cookie Gourmand</h3>
                    <p class="prix">4,90€</p>
                </div>


            </div>
        </div>

    </div>

    <div class="produits">

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Gateauaucitron">
            <img src="images/Gateauaucitron.jpg" alt="Gateau citron">
            </a>
            <div class="produit-info">
                      <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitGateauaucitron['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitGateauaucitron['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitGateauaucitron['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitGateauaucitron['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>
                <div>
                    <h3>Gâteau au citron</h3>
                    <p class="prix">5,50€ la part</p>
                </div>
                
            </div>
        </div>

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Tiramisu">
            <img src="images/Tiramisu.jpg" alt="tiramisu">
            </a>
            
            <div class="produit-info">
                      <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitTiramisu['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitTiramisu['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitTiramisu['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitTiramisu['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>
                <div>
                    <h3>Tiramisu</h3>
                    <p class="prix">6,90€ la part</p>
                </div>

                
            </div>
        </div>

    </div>
</section>
<section class="incontournable">

    <h2 class="titre-section">Les incontournables</h2>

    <div class="produits">

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Tarteauxpommes">
            <img src="images/Tarteauxpommes.jpg" alt="Tarte pomme">
            </a>
            
            <div class="produit-info">
                       <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitTarteauxpommes['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitTarteauxpommes['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitTarteauxpommes['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitTarteauxpommes['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>
                <div>
                    <h3>Tarte aux pommes</h3>
                    <p class="prix">À partir de 5,50€</p>
                </div>

                
            </div>
        </div>

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Cappuccino">
            <img src="images/Cappuccino.jpg" alt="Cappuccino">
            </a>
            
            <div class="produit-info">
                       <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitCappuccino['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitCappuccino['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitCappuccino['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitCappuccino['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>
                <div>
                    <h3>Cappuccino</h3>
                    <p class="prix">3,50€</p>
                </div>

                
            </div>
        </div>

    </div>    
<section class="produit-highlight">
    <a href="pageproduit/produits.php?nom=Tarteàlaframboise">
    <img src="images/Tarteàlaframboise.jpg" alt="Tarte framboise">
    </a>
    <div class="produit-info">
               <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitTartealaframboise['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitTartealaframboise['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitTartealaframboise['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitTartealaframboise['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>
                <div>
                    <h3>Tarte à la framboise</h3>
                    <p class="prix">6,90€ la part</p>
                </div>

                
            </div>
</section>
    
    <div class="produits">

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Macaron">
            <img src="images/Macaron.jpg" alt="Macaron">
            </a>
            
            <div class="produit-info">
                       <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitMacaron['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitMacaron['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitMacaron['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitMacaron['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>
                <div>
                    <h3>Macaron</h3>
                    <p class="prix">2,50€</p>
                </div>

                
            </div>
        </div>

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Parisbrest">
            <img src="images/Parisbrest.jpg" alt="Paris brest">
            </a>
            
            <div class="produit-info">                
                       <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitParisbrest['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitParisbrest['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitParisbrest['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitParisbrest['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>
                <div>
                    <h3>Paris-Brest</h3>
                    <p class="prix">4,50€</p>
                </div>

            </div>
        </div>

    </div>
</section>
    

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
<button id="btn-darkmode" class="btn-darkmode">☾</button>
<script src="darkmode.js"></script>
</body>
</html>
