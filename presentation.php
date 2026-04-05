<?php
 // Lire tous les produits
$fichierProduits = 'data/produits.json'; // ⚠️ adapte le chemin si nécessaire
$produits = json_decode(file_get_contents($fichierProduits), true);
?>

<?php // Fonction pour récupérer un produit depuis le JSON
function getProduit($nom, $produits) {
    foreach ($produits as $p) {
        if ($p['nom'] === $nom) {
            return $p;
        }
    }
    return null; // si produit introuvable
} ?>

<!-- formatage de chaque produit pour les petits paniers -->
<?php
$produitPainsuisse = getProduit('Painsuisse', $produits);
$produitPainauchocolat = getProduit('Painauchocolat', $produits);
$produitFeuilletecitron = getProduit('Feuilletécitron', $produits);
$produitCroissantauxamandes = getProduit('Croissantauxamandes', $produits);
$produitPainauxraisins = getProduit('Painauxraisins', $produits);
$produitCroissantaubeurre = getProduit('Croissantaubeurre', $produits);

$produitMatchalatte = getProduit('Matchalatte', $produits);
$produitCappuccino = getProduit('Cappuccino', $produits);
$produitMilkshake = getProduit('Milkshake', $produits);
$produitLattefraisematcha = getProduit('Lattefraisematcha', $produits);
$produitCafelatte = getProduit('Cafélatte', $produits);
$produitChocolatchaud = getProduit('Chocolatchaud', $produits);

$produitCroissantgourmand = getProduit('Croissantgourmand', $produits);
$produitCookiegourmand = getProduit('Cookiegourmand', $produits);
$produitCookie = getProduit('Cookie', $produits);

$produitOperapistacheyuzu = getProduit('Opérapistacheyuzu', $produits);
$produitEclairchocolat = getProduit('Eclairchocolat', $produits);
$produitMacaron = getProduit('Macaron', $produits);
$produitParisbrest = getProduit('Parisbrest', $produits);
$produitFlan = getProduit('Flan', $produits);
$produitTiramisu = getProduit('Tiramisu', $produits);

$produitBrowniechocolatnoisette = getProduit('Browniechocolatnoisette', $produits);
$produitRoulespeculoos = getProduit('Rouléspéculoos', $produits);
$produitBrownieube = getProduit('Brownieube', $produits);
$produitGateauaucitron = getProduit('Gateauaucitron', $produits);
$produitGateauauxfraises = getProduit('Gateauauxfraises', $produits);
$produitGateaudecrepeaumatcha = getProduit('Gateaudecrepeaumatcha', $produits);
$produitGateauauxfruits = getProduit('Gateauauxfruits', $produits);

$produitTarteauxpommes = getProduit('Tarteauxpommes', $produits);
$produitTarteaucitron = getProduit('Tarteaucitron', $produits);
$produitTartealaframboise = getProduit('Tarteàlaframboise', $produits);
$produitCheesecakeaumatcha = getProduit('Cheesecakeaumatcha', $produits);
$produitTarteauxpeches = getProduit('Tarteauxpeches', $produits);
$produitTarteauxmyrtilles = getProduit('Tarteauxmyrtilles', $produits);
$produitTartealanoisette = getProduit('Tarteàlanoisette', $produits);
?>

<?php

if (isset($_POST['ajouter_panier'])) {

    $nom = $_POST['nom']; //important (identifiant produit)
    $produit = $_POST['produit'];
    $prix = $_POST['prix'];
    $saveur = $_POST['saveur'];
    $quantite = $_POST['quantite'];

    // Lire le panier
    $fichier = 'data/panier.json'; // ⚠️ adapte le chemin

    if (file_exists($fichier)) {
        $panier = json_decode(file_get_contents($fichier), true);
    } else {
        $panier = [];
    }

    // Ajouter produit
    $panier[] = [
        "nom" => $nom, // 🔥 très important pour les liens ensuite
        "produit" => $produit,
        "prix" => $prix,
        "saveur" => $saveur,
        "quantite" => $quantite
    ];

    // Sauvegarder
    file_put_contents($fichier, json_encode($panier, JSON_PRETTY_PRINT));

    // 🔥 Recharge la page (évite bug F5)
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tous nos produits</title>

    <link rel="icon" href="">
    <link rel="stylesheet" href="couleurs.css">
    <link rel="stylesheet" href="structg.css">
    <link rel="stylesheet" href="cssaccueil.css">
</head>

<body>

    
    <div class="barres">
        <span></span>
        <span></span>
        <span></span>
    </div>


    
 <h1> <a href="accueil.php" class="logo">La Cour des Délices</a></h1>
    
    
<div class="top-icons">

    
    <div class="profil-menu">
        <img src="images/Iconprofil.png" alt="Profil" class="icon">

        <div class="profil-bulle">
            <a href="inscription.html">Inscription</a>
            <a href="connexion.html">Connexion</a>
        </div>
    </div>


    <a href="panier.php">
        <img src="images/Iconpanier.png" alt="Panier" class="icon" id="panier">
    </a>

</div>
    
<div class="search-bar">
    <input type="search" placeholder=" qu'est-ce qui vous ferait plaisir?">
    <button><img src="images/Iconloupe.png" alt="loupe"></button>
</div>
    </br>
    
    <nav class="menu-horizontal">
    <ul>
        <li>
            <a href="menu.html">Menus</a>
        </li>

        <li>
            <a href="presentation.php" class="active">Tous nos produits</a>
        </li>
    </ul>
</nav>    
    
<div class="categories">

     <a href="presentation.php#viennoiseries" class="categorie">
        <img src="images/Croissantaubeurre.jpg" alt="viennoiserie">
        <p>Viennoiseries</p>
    </a>

    <a href="presentation.php#boissons" class="categorie">
        <img src="images/Cafélatte.jpg" alt="Boissons">
        <p>Boissons</p>
    </a>

    <a href="presentation.php#gourmandises" class="categorie">
        <img src="images/Cookiegourmand.jpg" alt="Gourmandises">
        <p>Gourmandises</p>
    </a>
    
    <a href="presentation.php#patisseries" class="categorie">
        <img src="images/Eclairchocolat.jpg" alt="Patisseries">
        <p>Patisseries</p>
    </a>
    
    <a href="presentation.php#gateaux" class="categorie">
        <img src="images/Gateauauxfraises.jpg" alt="Gateaux">
        <p>Gateaux</p>
    </a>
    
    <a href="presentation.php#tartes" class="categorie">
        <img src="images/Tarteàlanoisette.jpg" alt="Tartes">
        <p>Tartes</p>
    </a>

</div>
     
<a href="" id="btn-top">↑</a>

<section class="vienoiserie">

    <h2 class="titre-section" id="viennoiseries">Vienoiseries</h2>

    <div class="produits">

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Painsuisse">
            <img src="images/Painsuisse.jpg" alt="Pain suisse">
            </a>
            
            <div class="produit-info">
               <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitPainsuisse ['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitPainsuisse['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitPainsuisse['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitPainsuisse['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

                
                <div>
                    <h3>Pain suisse</h3>
                    <p class="prix">2,90€</p>
                </div>
            </div>
        </div>

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Painauchocolat">
            <img src="images/Painauchocolat.jpg" alt="Pain au chocolat">
            </a>
            
            <div class="produit-info">
                
                <div class="produit-info">
               <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitPainauchocolat ['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitPainauchocolat['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitPainauchocolat['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitPainauchocolat['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

                
                <div>
                    <h3>Pain au chocolat</h3>
                    <p class="prix">2,50€</p>
                </div>              
            </div>
        </div>

    </div>

</section>
    
<section class="produit-highlight">
     <a href="pageproduit/produits.php?nom=Feuilletécitron">
    <img src="images/Feuilletécitron.jpg" alt="feuillete citron">
    </a>
    
    <div class="produit-info">
       <div class="produit-info">
               <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitFeuilletecitron ['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitFeuilletecitron['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitFeuilletecitron['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitFeuilletecitron['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

        
        <div>
          <h3>feuilleté citron</h3>
           <p class="prix">3,90€</p>
            
        </div>          
      </div>

</section>

<div class="produits">

        <div class="produit">
             <a href="pageproduit/produits.php?nom=Croissantauxamandes">
            <img src="images/Croissantauxamandes.jpg" alt="Croissant Amandes">
            </a>
            
            <div class="produit-info">
               <!-- MENU PANIER -->
                <div class="panier-menu">
                    <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

                    <!-- BULLE -->
                    <div class="panier-bulle">
                    <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitCroissantauxamandes ['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitCroissantauxamandes['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitCroissantauxamandes['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitCroissantauxamandes['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>
 
                <div>
                    <h3>Croissant aux amandes</h3>
                    <p class="prix">3,50€</p>
                </div>
            </div>
        </div>

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Painauxraisins">
            <img src="images/Painauxraisins.jpg" alt="Pain aux raisins">
            </a>
            
            <div class="produit-info">
                
                <div class="produit-info">
               <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitPainauxraisins ['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitPainauxraisins['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitPainauxraisins['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitPainauxraisins ['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

                
                <div>
                    <h3>Pain aux raisins</h3>
                    <p class="prix">2,90€</p>
                </div>              
            </div>
        </div>

    </div>

</section>
<section class="produit-highlight">
    <a href="pageproduit/produits.php?nom=Croissantaubeurre">
    <img src="images/Croissantaubeurre.jpg" alt="Croissant">
    </a>
    
    <div class="produit-info">
        <div class="produit-info">
               <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitCroissantaubeurre ['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitCroissantaubeurre['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitCroissantaubeurre['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitCroissantaubeurre['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

        
        <div>
          <h3>Croissant au beurre</h3>
           <p class="prix">1,50€</p>
            
        </div>          
      </div>

</section>

<section class="Boisson">

    <h2 class="titre-section" id="boissons">Boissons</h2>

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

    <input type="hidden" name="nom" value="<?=$produitMatchalatte ['nom'] ?>">
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
                    <p class="prix">5.50€</p>
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

<div class="produits">

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Milkshake">
            <img src="images/Milkshake.jpg" alt="milkshake">
            </a>
            
               <div class="produit-info">
               <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitMilkshake ['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitMilkshake['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitMilkshake['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitMilkshake['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

                <div>
                    <h3>Milkshake</h3>
                    <p class="prix">5,90€</p>
                </div>
            </div>
        </div>

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Lattefraisematcha">
            <img src="images/Lattefraisematcha.jpg" alt="Latte fraise matcha">
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
    <div class="produits">

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Cafélatte">
            <img src="images/Cafélatte.jpg" alt="Café latte">
            </a>
                <div class="produit-info">
               <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitCafelatte ['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitCafelatte['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitCafelatte['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitCafelatte['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

                <div>
                    <h3>Café Latte</h3>
                    <p class="prix">3,90€</p>
                </div>
                
            </div>
        </div>

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Chocolatchaud">
            <img src="images/Chocolatchaud.jpg" alt="Chocolat Chaud">
            </a>
            
                <div class="produit-info">
               <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitChocolatchaud['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitChocolatchaud['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitChocolatchaud['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitChocolatchaud['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

                <div>
                    <h3>Chocolat Chaud</h3>
                    <p class="prix">2,50€</p>
                </div> 
            </div>
        </div>
    </div>
</section>

<section class="Gourmandises">

    <h2 class="titre-section" id="gourmandises">Gourmandises</h2>

    <div class="produits">

        <div class="produit">
             <a href="pageproduit/produits.php?nom=Croissantgourmand">
            <img src="images/Croissantgourmand.jpg" alt="Croissant Gourmand">
            </a>
                
                <div class="produit-info">
               <!-- MENU PANIER -->
                <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?=$produitCroissantgourmand ['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitCroissantgourmand['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitCroissantgourmand['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitCroissantgourmand['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

                
                <div>
                    <h3>Croissant Gourmand</h3>
                    <p class="prix">4,90€</p>
                </div>
            </div>
        </div>

        <div class="produit">
             <a href="pageproduit/produits.php?nom=Cookiegourmand">
            <img src="images/Cookiegourmand.jpg" alt="Cookie gourmand">
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
                    <p class="prix">4,50€</p>
                </div>              
            </div>
        </div>

    </div>

</section>

<section class="patisserie">

    <h2 class="titre-section" id="patisseries">Patisseries</h2>

    <div class="produits">

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Cookie">
            <img src="images/Cookie.jpg" alt="Cookie">
            </a>
            
               <div class="produit-info">
               <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?=$produitCookie ['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitCookie['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitCookie['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitCookie['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

                <div>
                    <h3>Cookie</h3>
                    <p class="prix">2,90€ </p>
                </div>
            </div>
        </div>

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Opérapistacheyuzu">
            <img src="images/Opérapistacheyuzu.jpg" alt="Opéra pistache Yuzu">
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

    </div>    
<section class="produit-highlight">
   <a href="pageproduit/produits.php?nom=Eclairchocolat">
    <img src="images/Eclairchocolat.jpg" alt="Eclair chocolat">
    </a>

        <div class="produit-info">
               <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitEclairchocolat['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitEclairchocolat['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitEclairchocolat['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitEclairchocolat['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

                <div>
                    <h3>Éclair au chocolat</h3>
                    <p class="prix">3,50€</p>
                </div>
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
    <div class="produits">

        <div class="produit">
           <a href="pageproduit/produits.php?nom=Flan">
            <img src="images/Flan.jpg" alt="Flan">
            </a>
            
                <div class="produit-info">
               <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitFlan['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitFlan['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitFlan['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitFlan['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

                <div>
                    <h3>Flan à la vanille</h3>
                    <p class="prix">4,50€ la part</p>
                </div>

                
            </div>
        </div>

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Tiramisu">
            <img src="images/Tiramisu.jpg" alt="Tiramisu">
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

<section class="Gateaux">

    <h2 class="titre-section" id="gateaux">Gâteaux</h2>

    <div class="produits">
         <a href="pageproduit/produits.php?nom=Browniechocolatnoisette">
        <div class="produit">
            
            <img src="images/Browniechocolatnoisette.jpg" alt="Brownie">
            </a>
            
                <div class="produit-info">
               <!-- MENU PANIER -->
                <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitBrowniechocolatnoisette['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitBrowniechocolatnoisette['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitBrowniechocolatnoisette['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitBrowniechocolatnoisette['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

                <div>
                    <h3>Brownie chocolat & noisette</h3>
                    <p class="prix">3,50€ la part</p>
                </div>

                
            </div>
        </div>

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Rouléspéculoos">
            <img src="images/Rouléspéculoos.jpg" alt="Roulé spéculoos">
            </a>
            
                <div class="produit-info">
               <!-- MENU PANIER -->
            <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitRoulespeculoos['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitRoulespeculoos['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitRoulespeculoos['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitRoulespeculoos['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

                <div>
                    <h3>Roulé spéculoos</h3>
                    <p class="prix">5,90€ la part</p>
                </div>
            </div>
        </div>

    </div>    
<section class="produit-highlight">
    <a href="pageproduit/produits.php?nom=Brownieube">
    <img src="images/Brownieube.jpg" alt="Brownie Ube">
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
                    <h3>Brownie à l'Ube</h3>
                    <p class="prix">4,90€ la part</p>
                </div>
            </div>
        </div>
</section>
    
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
                    <p class="prix">6,90€ la part</p>
                </div>

                
            </div>
        </div>

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Gateauauxfraises">
            <img src="images/Gateauauxfraises.jpg" alt="Gateau fraise">
            </a>
                           
                <div class="produit-info">
               <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitGateauauxfraises['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitGateauauxfraises['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitGateauauxfraises['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitGateauauxfraises['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

                <div>
                    <h3>Gâteau aux fraises</h3>
                    <p class="prix">6,90€ la part</p>
                </div>

            </div>
        </div>

    </div>
    <div class="produits">

        <div class="produit">
             <a href="pageproduit/produits.php?nom=Gateaudecrepeaumatcha">
            <img src="images/Gateaudecrepeaumatcha.jpg" alt="Gateau crepe matcha">
            </a>
            
                <div class="produit-info">
               <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitGateaudecrepeaumatcha['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitGateaudecrepeaumatcha['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitGateaudecrepeaumatcha['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitGateaudecrepeaumatcha['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

                <div>
                    <h3>Gâteau de crêpes au matcha</h3>
                    <p class="prix">7,90€ la part</p>
                </div>

                
            </div>
        </div>

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Gateauauxfruits">
            <img src="images/Gateauauxfruits.jpg" alt="Gateau fruit">
            </a>
                           
                <div class="produit-info">
               <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitGateauauxfruits['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitGateauauxfruits['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitGateauauxfruits['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitGateauauxfruits['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

                <div>
                    <h3>Gâteau aux fruits</h3>
                    <p class="prix">8,90€ la part</p>
                </div>

            </div>
        </div>

    </div>
    
</section>

<section class="Tartes">

    <h2 class="titre-section" id="tartes">Tartes</h2>

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
                    <p class="prix">5,50€ la part</p>
                </div>

                
            </div>
        </div>

        <div class="produit">
             <a href="pageproduit/produits.php?nom=Tarteaucitron">
            <img src="images/Tarteaucitron.jpg" alt="Tarte citron">
            </a>
            
                <div class="produit-info">
               <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitTarteaucitron['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitTarteaucitron['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitTarteaucitron['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitTarteaucitron['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

                <div>
                    <h3>Tarte au citron</h3>
                    <p class="prix">5,50€ la part</p>
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
             <a href="pageproduit/produits.php?nom=Cheesecakeaumatcha">
            <img src="images/Cheesecakeaumatcha.jpg" alt="Cheesecake matcha">
            </a>
            
                <div class="produit-info">
               <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitCheesecakeaumatcha['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitCheesecakeaumatcha['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitCheesecakeaumatcha['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitCheesecakeaumatcha['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

                <div>
                    <h3>Cheesecake au matcha</h3>
                    <p class="prix">7,90€ la part</p>
                </div>  
            </div>
        </div>

        <div class="produit">
             <a href="pageproduit/produits.php?nom=Tarteauxpeches">
            <img src="images/Tarteauxpeches.jpg" alt="Tarte peche">
            </a>
                            
                <div class="produit-info">
               <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitTarteauxpeches['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitTarteauxpeches['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitTarteauxpeches['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitTarteauxpeches['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

                <div>
                    <h3>Tarte aux pêches</h3>
                    <p class="prix">8,90€ la part</p>
                </div>

            </div>
        </div>

    </div>
    <div class="produits">

        <div class="produit">
             <a href="pageproduit/produits.php?nom=Tarteauxmyrtilles">
            <img src="images/Tarteauxmyrtilles.jpg" alt="Tarte myrtille">
            </a>
            
                <div class="produit-info">
               <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitTarteauxmyrtilles['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitTarteauxmyrtilles['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitTarteauxmyrtilles['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitTarteauxmyrtilles['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

                <div>
                    <h3>Tarte aux myrtilles</h3>
                    <p class="prix">9,90€ la part</p>
                </div>    
            </div>
        </div>

        <div class="produit">
            <a href="pageproduit/produits.php?nom=Tarteàlanoisette">
            <img src="images/Tarteàlanoisette.jpg" alt="Tarte noisette">
            </a>
                            
                <div class="produit-info">
               <!-- MENU PANIER -->
        <div class="panier-menu">

            <img src="images/Iconpanier.png" class="icon-panier" alt="Ajouter">

            <!-- BULLE -->
            <div class="panier-bulle">

               <form method="POST">

    <input type="hidden" name="nom" value="<?= $produitTartealanoisette['nom'] ?>">
    <input type="hidden" name="produit" value="<?= $produitTartealanoisette['titre'] ?>">
    <input type="hidden" name="prix" value="<?= number_format($produitTartealanoisette['prix'], 2) ?>">

    <label>Quantité :</label>
    <input type="number" name="quantite" value="1" min="1" max="20">

    <label>Saveur :</label>
    <select name="saveur">
        <?php foreach ($produitTartealanoisette['saveurs'] as $saveur): ?>
            <option value="<?= $saveur ?>"><?= $saveur ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="ajouter_panier">Ajouter</button>
</form>

            </div>
        </div>

                <div>
                    <h3>Tarte à la noisette</h3>
                    <p class="prix">9,90€ la part</p>
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
