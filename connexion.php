<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="connexion.css">
    <link rel="stylesheet" href="structg.css">
 	<link rel="stylesheet" href="couleurs.css">
	<title>Connexion</title>
</head>

<body>

    <?php
        session_start(); // ✅ Démarrage de la session

        $erreur = [];
        
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // EMAIL
            if ($_POST["mail"] == "") {
                $erreur["mail"] = "Veuillez renseigner ce champ";
            }
            // MDP
            if ($_POST["mdp"] == "") {
                $erreur["mdp"] = "Veuillez renseigner ce champ";
            }
            
            if (empty($erreur)) {
                $mail = htmlspecialchars($_POST["mail"]);
                $mdp = $_POST["mdp"];
                
                // Lire le fichier JSON
                $fichier = "infoclient.json";
                $connecte = false;
                $utilisateurTrouve = null;
                
                if (file_exists($fichier)) {
                    $contenu = file_get_contents($fichier);
                    $utilisateurs = json_decode($contenu, true);
                    
                    if (is_array($utilisateurs)) {
                        foreach ($utilisateurs as $utilisateur) {
                            if ($utilisateur && $utilisateur["mail"] == $mail) {
                                // ✅ password_verify compare le mdp saisi avec le hash stocké
                                if (password_verify($mdp, $utilisateur["mdp"])) {
                                    $connecte = true;
                                    $utilisateurTrouve = $utilisateur;
                                    break;
                                }
                            }
                        }
                    }
                }
                
                if ($connecte) {
                    // ✅ Stocker les infos utiles en session
                    $_SESSION["connecte"] = true;
                    $_SESSION["id"] = $utilisateurTrouve["id"];
                    $_SESSION["mail"] = $utilisateurTrouve["mail"];
                    $_SESSION["nom"] = $utilisateurTrouve["nom"] ?? ""; // si tu as ce champ

                    // ✅ Redirection vers l'accueil (corrigé : guillemets autour de l'id)
                    header("Location: accueil.php");
                    exit();
                } else {
                    $erreur["mail"] = "E-mail ou mot de passe incorrect";
                    $erreur["mdp"] = "E-mail ou mot de passe incorrect";
                }
            }
        }
    ?>


    <header>
            <div class="barres">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <h1><a href="accueil.html" class="logo">La Cour des Délices</a></h1>
    
            <div class="top-icons">
                <div class="profil-menu">
                    <img src="images/Iconprofil.png" alt="Profil" class="icon">
                    <div class="profil-bulle">
                        <a href="inscription.html">Inscription</a>
                        <a href="connexion.html">Connexion</a>
                    </div>
                </div>
                <a href="">
                <img src="images/Iconpanier.png" alt="Panier" class="icon" id="panier">
                </a>
            </div>
        </header>
    
    <main>
        <form action="connexion.php" method="POST">
            <fieldset>
                <legend>Connexion</legend>
                <div class="champ">
                    E-mail *
                    <br />
                    <input type="email" id="mail" name="mail" placeholder="nom@email.com" value="<?= isset($_POST['mail']) ? htmlspecialchars($_POST['mail']) : '' ?>" class="<?= isset($erreur['mail']) ? 'erreur' : '' ?>" />
                    <small><?= $erreur['mail'] ?? '' ?></small>
                </div>
                <div class="champ">
                    Mot de passe *
                    <br />
                    <input type="password" id="mdp" name="mdp" maxlength=20 class="<?= isset($erreur['mdp']) ? 'erreur' : '' ?>" />
                    <small><?= $erreur['mdp'] ?? '' ?></small>
                    <img src="images/oeil.png" alt="Afficher mot de passe">
                </div>
                <a class="lien" href="mdpoublie.html">Mot de passe oublié ?</a>
                <br />
                <input class="bouton" type="submit" value="ME CONNECTER"/>
            </fieldset>
        </form> 
        <p class="connexion">
            Vous n'êtes toujours pas client chez nous? 
            <br />
            Créez un compte en quelques clics pour profiter pleinement des avantages des avantages du site
        </p>
        <a class="bouton" href="inscription.html">CRÉER UN COMPTE</a>
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
