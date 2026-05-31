<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../fichiers_css/connexion.css">
    <link rel="stylesheet" href="../fichiers_css/structg.css">
 	<link rel="stylesheet" href="../fichiers_css/couleurs.css">
	<title>Mot de passe oublié</title>
</head>

<body>
    <?php
        $erreur = [];

        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            // Vérifier si le champ est vide
            if ($_POST["mail"] == "") {
                $erreur["mail"] = "Veuillez renseigner ce champ";
            }

            if (empty($erreur)) {

                $mail = htmlspecialchars($_POST["mail"]);
                $fichier = "../data/infoclient.json";
                $trouve = false;

                if (file_exists($fichier)) {
                    $contenu = file_get_contents($fichier);
                    $utilisateurs = json_decode($contenu, true);

                    if (is_array($utilisateurs)) {
                        foreach ($utilisateurs as $utilisateur) {
                            if ($utilisateur["mail"] == $mail) {
                                $trouve = true;
                                break;
                            }
                        }
                    }
                }

                if ($trouve) {
                    // Redirection vers la page de connexion
                    header("Location: ../fichiers_php/connexion.php");
                    exit();
                } else {
                    $erreur["mail"] = "Email non connu";
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

            <h1><a href="../fichiers_php/accueil.php" class="logo">La Cour des Délices</a></h1>
    
            <div class="top-icons">
                <!-- PROFIL -->
                <div class="profil-menu">
                    <img src="../images/Iconprofil.png" alt="Profil" class="icon">

                    <div class="profil-bulle">
                        <a href="../fichiers_php/inscription.php">Inscription</a>
                        <a href="../fichiers_php/connexion.php">Connexion</a>
                    </div>
                </div>

                <!-- PANIER -->
                <a href="">
                <img src="../images/Iconpanier.png" alt="Panier" class="icon" id="panier">
                </a>
            </div>
        </header>

    <main>
        <h2>Mot de passe oublié ?</h2>
        <form action="../fichiers_php/mdpoublie.php" method="POST">
            <fieldset>
                <div class="champ">
                    E-mail 
                    <br />
                    <input type="email" id="mail" name="mail" placeholder="nom@email.com" value="<?= isset($_POST['mail']) ? htmlspecialchars($_POST['mail']) : '' ?>"
                class="<?= isset($erreur['mail']) ? 'erreur' : '' ?>" />
                    <small><?= $erreur['mail'] ?? '' ?></small>
                </div>
                <input class="bouton" type="submit" value="ENVOYER UN LIEN RÉINITIALISATION"/>
            </fieldset>
        </form>
    </main>

    <footer>
        <p>suivez nous sur nos réseaux!
            </br>
                <img src="../images/Iconinstagram.jpg" alt="instagram" class="icon">
                <img src="../images/Icontiktok.jpg" alt="tiktok" class="icon">
                <img src="../images/Icontwitter.png" alt="twitter" class="icon">
        </p>
        <div class="infos-footer">

            <div class="info">
                <img src="../images/Iconlocalisation.png" alt="maps" class="icon">
                <span>5 avenue de la république, 75015 Paris</span>
            </div>

            <div class="info">
                <img src="../images/Iconhorloge.png" alt="horloge" class="icon">
                <span>Tous les jours 9h - 20h</span>
            </div>
        </div>
        <h5>© 2026 Pâtisserie</h5>
    </footer>

</body>
</html>
