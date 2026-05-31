<?php
session_start();

// Redirection si non connecté
if (!isset($_SESSION['id'])) {
    header("Location: ../fichiers_php/connexion.php");
    exit();
}

// Récupérer la référence depuis l'URL
$reference = isset($_GET['ref']) ? htmlspecialchars($_GET['ref']) : '';
if (!$reference) {
    header("Location: ../fichiers_php/profil2.php");
    exit();
}

// Charger l'utilisateur connecté
$users = json_decode(file_get_contents("../data/infoclient.json"), true);
$userTrouve = null;
foreach ($users as $user) {
    if ($user['id'] == $_SESSION['id']) {
        $userTrouve = $user;
        break;
    }
}

// Vérifier que la commande appartient bien à cet utilisateur et qu'elle est livrée
$commandes = json_decode(file_get_contents("../data/commande.json"), true);
$commandeTrouvee = null;
foreach ($commandes as $commande) {
    if (
        $commande['reference'] === $reference &&
        strtolower($commande['nom'])    === strtolower($userTrouve['nom']) &&
        strtolower($commande['prenom']) === strtolower($userTrouve['prenom'])
    ) {
        $commandeTrouvee = $commande;
        break;
    }
}

// Sécurité : si commande introuvable ou pas livrée, on redirige
if (!$commandeTrouvee || $commandeTrouvee['statut'] !== 'terminee') {
    header("Location: ../fichiers_php/profil2.php");
    exit();
}

// Charger les notations existantes
$fichierNotations = "../data/notations.json";
$notations = [];
if (file_exists($fichierNotations)) {
    $notations = json_decode(file_get_contents($fichierNotations), true) ?? [];
}

// Chercher si cet utilisateur a déjà noté cette commande
$notationExistante = null;
foreach ($notations as $notation) {
    if ($notation['reference'] === $reference && $notation['id_utilisateur'] == $_SESSION['id']) {
        $notationExistante = $notation;
        break;
    }
}

// Traitement du formulaire
$confirmation = false;
$erreurForm = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$notationExistante) {
    $noteLivraison    = isset($_POST['livraison'])    ? intval($_POST['livraison'])    : 0;
    $noteProduit      = isset($_POST['produit'])      ? intval($_POST['produit'])      : 0;
    $noteSatisfaction = isset($_POST['satisfaction']) ? intval($_POST['satisfaction']) : 0;

    if ($noteLivraison === 0 || $noteProduit === 0 || $noteSatisfaction === 0) {
        $erreurForm = "Veuillez attribuer une note à chaque catégorie.";
    } else {
        $nouvelleNotation = [
            "reference"       => $reference,
            "id_utilisateur"  => $_SESSION['id'],
            "livraison"       => [
                "note"        => $noteLivraison,
                "commentaire" => htmlspecialchars(trim($_POST['commentaire_livraison'] ?? ''))
            ],
            "produit"         => [
                "note"        => $noteProduit,
                "commentaire" => htmlspecialchars(trim($_POST['commentaire_produit'] ?? ''))
            ],
            "satisfaction"    => [
                "note"        => $noteSatisfaction,
                "commentaire" => htmlspecialchars(trim($_POST['commentaire_satisfaction'] ?? ''))
            ]
        ];

        $notations[] = $nouvelleNotation;
        file_put_contents($fichierNotations, json_encode($notations, JSON_PRETTY_PRINT));

        // On recharge la notation pour afficher la confirmation
        $notationExistante = $nouvelleNotation;
        $confirmation = true;
    }
}

// Fonction pour afficher des étoiles
function afficherEtoiles($note) {
    $html = '<span class="etoiles-affichage">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $note
            ? '<span class="pleine">★</span>'
            : '<span class="vide">★</span>';
    }
    $html .= '</span>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notations</title>
    <link rel="stylesheet" href="../fichiers_css/structg.css">
    <link rel="stylesheet" href="../fichiers_css/couleurs.css">
    <link rel="stylesheet" href="../fichiers_css/notation.css">
    <link rel="stylesheet" href="../fichiers_css/darkmode.css">
<link rel="stylesheet" href="../fichiers_css/darkmode_notation.css">

    <style>
        /* Étoiles statiques pour affichage note existante */
        .etoiles-affichage { font-size: 1.6em; }
        .etoiles-affichage .pleine { color: var(--article-background); }
        .etoiles-affichage .vide   { color: #ccc; }

        .bouton-submit { padding: 10px 24px; cursor: pointer; }
        .note-detail { background: var(--main-background); padding: 16px; border-radius: 8px; margin-bottom: 16px; }
        .note-detail p { margin: 6px 0; color: #666; font-style: italic; }
        .retour { display: inline-block; margin-bottom: 20px; color: #666; }
        .confirmation { background: #d4edda; color: #155724; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <header>
        <div class="barres">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <h1><a href="accueil.html" class="logo">La Cour des Délices</a></h1>
    
        <div class="top-icons">
            <!-- PROFIL -->
            <div class="profil-menu">
                <img src="../images/Iconprofil.png" alt="Profil" class="icon">

                <div class="profil-bulle">
                    <a href="inscription.html">Inscription</a>
                    <a href="connexion.html">Connexion</a>
                </div>
            </div>

            <!-- PANIER -->
            <a href="">
            <img src="../images/Iconpanier.png" alt="Panier" class="icon" id="panier">
            </a>
        </div>
    </header>

    <section>
        <div class="review">
            <a class="retour" href="../fichiers_php/profil2.php">← Retour à mes commandes</a>
            <h2>Commande <?= $reference ?></h2>
            
            <?php if ($confirmation) : ?>
                <p class="confirmation">✅ Merci pour votre avis, il a bien été enregistré !</p>
            <?php endif; ?>

    <?php if ($notationExistante) : ?>
                <!-- Affichage de la note déjà soumise -->
                <h3>Votre avis</h3>

                <div class="note-detail">
                    <h4>Livraison <?= afficherEtoiles($notationExistante['livraison']['note']) ?></h4>
                    <?php if ($notationExistante['livraison']['commentaire']) : ?>
                        <p>"<?= $notationExistante['livraison']['commentaire'] ?>"</p>
                    <?php endif; ?>
                </div>

                <div class="note-detail">
                    <h4>Produit <?= afficherEtoiles($notationExistante['produit']['note']) ?></h4>
                    <?php if ($notationExistante['produit']['commentaire']) : ?>
                        <p>"<?= $notationExistante['produit']['commentaire'] ?>"</p>
                    <?php endif; ?>
                </div>

                <div class="note-detail">
                    <h4>Satisfaction générale <?= afficherEtoiles($notationExistante['satisfaction']['note']) ?></h4>
                    <?php if ($notationExistante['satisfaction']['commentaire']) : ?>
                        <p>"<?= $notationExistante['satisfaction']['commentaire'] ?>"</p>
                    <?php endif; ?>
                </div>

            <?php else : ?>
                <!-- Formulaire de notation -->
                <?php if ($erreurForm) : ?>
                    <p style="color: red;"><?= $erreurForm ?></p>
                <?php endif; ?>

                <form action="../fichiers_php/notation.php?ref=<?= $reference ?>" method="POST">

                    <div class="review-block">
                        <h3>Livraison</h3>
                        <div class="rating">
                            <input type="radio" name="livraison" id="liv5" value="5"><label for="liv5">★</label>
                            <input type="radio" name="livraison" id="liv4" value="4"><label for="liv4">★</label>
                            <input type="radio" name="livraison" id="liv3" value="3"><label for="liv3">★</label>
                            <input type="radio" name="livraison" id="liv2" value="2"><label for="liv2">★</label>
                            <input type="radio" name="livraison" id="liv1" value="1"><label for="liv1">★</label>
                        </div>
                        <textarea name="commentaire_livraison" placeholder="Votre commentaire (optionnel)..."></textarea>
                    </div>

                    <div class="review-block">
                        <h3>Produit</h3>
                        <div class="rating">
                            <input type="radio" name="produit" id="prod5" value="5"><label for="prod5">★</label>
                            <input type="radio" name="produit" id="prod4" value="4"><label for="prod4">★</label>
                            <input type="radio" name="produit" id="prod3" value="3"><label for="prod3">★</label>
                            <input type="radio" name="produit" id="prod2" value="2"><label for="prod2">★</label>
                            <input type="radio" name="produit" id="prod1" value="1"><label for="prod1">★</label>
                        </div>
                        <textarea name="commentaire_produit" placeholder="Votre commentaire (optionnel)..."></textarea>
                    </div>

                    <div class="review-block">
                        <h3>Satisfaction générale</h3>
                        <div class="rating">
                            <input type="radio" name="satisfaction" id="sat5" value="5"><label for="sat5">★</label>
                            <input type="radio" name="satisfaction" id="sat4" value="4"><label for="sat4">★</label>
                            <input type="radio" name="satisfaction" id="sat3" value="3"><label for="sat3">★</label>
                            <input type="radio" name="satisfaction" id="sat2" value="2"><label for="sat2">★</label>
                            <input type="radio" name="satisfaction" id="sat1" value="1"><label for="sat1">★</label>
                        </div>
                        <textarea name="commentaire_satisfaction" placeholder="Votre commentaire (optionnel)..."></textarea>
                    </div>

                    <button type="submit" class="bouton-submit">Envoyer mon avis</button>
                </form>

            <?php endif; ?>
        </div> 
    </section>  

    <footer>
        <p>suivez nous sur nos réseaux!
            </br>
            <img src="../images/Iconinstagram.jpg" alt="instagram" class="icon">
            <img src="../images/Icontiktok.jpg" alt="tiktok" class="icon">
            <img src="../images/Iconinstagram.jpg" alt="instagram" class="icon">
        </p>
        <div class="infos-footer">
            <div class="info">
                <img src="../images/Iconlocalisation.png" alt="maps" class="icon">
                <span>5 av de la république, 75300 Paris</span>
            </div>
            <div class="info">
                <img src="../images/Iconhorloge.png" alt="horloge" class="icon">
                <span>Tous les jours 9h - 22h</span>
            </div>
        </div>
        <h5>© 2026 Pâtisserie</h5>    
    </footer>
<button id="btn-darkmode" class="btn-darkmode">☾</button>
<script src="../fichiers_js/darkmode.js"></script>
</body>
</html>
