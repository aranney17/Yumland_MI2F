<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="fichiers_css/connexion.css">
    <link rel="stylesheet" href="fichiers_css/structg.css">
    <link rel="stylesheet" href="fichiers_css/couleurs.css">
    <link rel="stylesheet" href="fichiers_css/darkmode.css">
    <link rel="stylesheet" href="fichiers_css/darkmode_connexion.css">
    <script src="fichiers_js/connexion.js" defer></script>
    <title>Connexion</title>
</head>
<body>

<?php
session_start();
$erreur = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST["mail"] == "") $erreur["mail"] = "Veuillez renseigner ce champ";
    if ($_POST["mdp"]  == "") $erreur["mdp"]  = "Veuillez renseigner ce champ";

    if (empty($erreur)) {
        $mail = strtolower(trim($_POST["mail"]));
        $mdp  = $_POST["mdp"];

        $fichier = "data/infoclient.json";
        $connecte = false;
        $utilisateurTrouve = null;

        if (file_exists($fichier)) {
            $utilisateurs = json_decode(file_get_contents($fichier), true) ?? [];
            foreach ($utilisateurs as $u) {
                if (strtolower($u["mail"]) === $mail) {
                    if (password_verify($mdp, $u["mdp"])) {
                        $connecte = true;
                        $utilisateurTrouve = $u;
                        break;
                    }
                }
            }
        }

        if ($connecte) {
            /*  refuser la connexion si le compte est bloque. */
            if ($utilisateurTrouve['bloque'] ?? false) {
                $erreur["mail"] = "Votre compte a été bloqué. Contactez l'administrateur.";
                $erreur["mdp"]  = "";
            } else {
                // Mise a jour date de connexion
                foreach ($utilisateurs as &$user) {
                    if ($user["id"] == $utilisateurTrouve["id"]) {
                        $user["dateconnexion"] = date("Y-m-d");
                        break;
                    }
                }
                unset($user);
                file_put_contents($fichier, json_encode($utilisateurs, JSON_PRETTY_PRINT));

                $_SESSION["connecte"] = true;
                $_SESSION["id"]       = $utilisateurTrouve["id"];
                $_SESSION["mail"]     = $utilisateurTrouve["mail"];
                $_SESSION["nom"]      = $utilisateurTrouve["nom"] ?? "";
                $_SESSION["role"]     = $utilisateurTrouve["role"];

                if      ($_SESSION["role"] == "client")         header("Location: accueil.php");
                elseif  ($_SESSION["role"] == "cuisinier")      header("Location: commandes.php");
                elseif  ($_SESSION["role"] == "administrateur") header("Location: administrateur.php");
                elseif  ($_SESSION["role"] == "livreur")        header("Location: livraison.php");
                exit();
            }
        } else {
            $erreur["mail"] = "E-mail ou mot de passe incorrect";
            $erreur["mdp"]  = "E-mail ou mot de passe incorrect";
        }
    }
}
?>

<header>
    <div class="barres"><span></span><span></span><span></span></div>
    <h1><a href="fichiers_php/accueil.php" class="logo">La Cour des Délices</a></h1>
    <div class="top-icons">
        <div class="profil-menu">
            <img src="images/Iconprofil.png" alt="Profil" class="icon">
            <div class="profil-bulle">
                <a href="fichiers_php/inscription.php">Inscription</a>
                <a href="fichiers_php/connexion.php">Connexion</a>
            </div>
        </div>
        <a href=""><img src="images/Iconpanier.png" alt="Panier" class="icon" id="panier"></a>
    </div>
</header>

<?php if (isset($_GET['inscription']) && $_GET['inscription'] === 'ok'): ?>
    <div class="message-succes">
        ✓ Votre inscription a bien été prise en compte. Vous pouvez maintenant vous connecter.
    </div>
<?php endif; ?>

<main>
    <form action="fichiers_php/connexion.php" method="POST" onsubmit="return verificationConnexion()">
        <fieldset>
            <legend>Connexion</legend>
            <div class="champ">
                E-mail *
                <br />
                <input type="email" id="mail" name="mail" placeholder="nom@email.com" value="<?= htmlspecialchars($_POST['mail'] ?? '') ?>" class="<?= isset($erreur['mail']) ? 'erreur' : '' ?>" />
                <small class="erreur" id="erreurmail"><?= $erreur['mail'] ?? '' ?></small>
            </div>
            <div class="champ">
                Mot de passe *
                <br />
                <input type="password" id="mdp" name="mdp" minlength="8" maxlength="20" oninput="compteur('mdp','compteurmdp',20)" class="<?= isset($erreur['mdp']) ? 'erreur' : '' ?>" />
                <img src="images/oeil.png" alt="Afficher mot de passe" onclick="visibilitemdp('mdp', this)">
                <small class="erreur" id="erreurmdp"><?= $erreur['mdp'] ?? '' ?></small>
                <small class="compteur" id="compteurmdp">0 / 20 caractères</small>
            </div>
            <a class="lien" href="mdpoublie.php">Mot de passe oublié ?</a>
            <br />
            <input class="bouton" type="submit" value="ME CONNECTER"/>
        </fieldset>
    </form>
    <p class="connexion">
        Vous n'êtes toujours pas client chez nous ?<br />
        Créez un compte en quelques clics.
    </p>
    <a class="bouton" href="inscription.php">CRÉER UN COMPTE</a>
</main>

<footer>
    <p>suivez nous sur nos réseaux!<br>
        <img src="images/Iconinstagram.jpg" class="icon">
        <img src="images/Icontiktok.jpg" class="icon">
        <img src="images/Icontwitter.png" class="icon">
    </p>
    <div class="infos-footer">
        <div class="info"><img src="images/Iconlocalisation.png" class="icon"><span>5 av de la république, 75015 Paris</span></div>
        <div class="info"><img src="images/Iconhorloge.png" class="icon"><span>Tous les jours 9h - 20h</span></div>
    </div>
    <h5>© 2026 Pâtisserie</h5>
</footer>

<button id="btn-darkmode" class="btn-darkmode">☾</button>
<script src="darkmode.js"></script>
</body>
</html>
