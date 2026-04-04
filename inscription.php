<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="connexion.css">
    <link rel="stylesheet" href="structg.css">
    <link rel="stylesheet" href="couleurs.css">
    <title>Inscription</title>
</head>

<body>

   <?php

    $erreur = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!isset($_POST["civilite"])) {
            $erreur["civilite"] = "Veuillez choisir une option";
        }
        // PRENOM
        if ($_POST["prenom"] == "") {
            $erreur["prenom"] = "Veuillez renseigner ce champ";
        }
        // NOM
        if ($_POST["nom"] == "") {
            $erreur["nom"] = "Veuillez renseigner ce champ";
        }
        // DATE
        if ($_POST["anniv"] == "") {
            $erreur["anniv"] = "Veuillez renseigner ce champ";
        } elseif ($_POST["anniv"] > date("Y-m-d")) {
            $erreur["anniv"] = "Date invalide";
        }
        // TELEPHONE - Suppression des espaces avant vérification
        if ($_POST["tel"] != "") {
            // Supprimer tous les espaces du numéro
            $tel = str_replace(" ", "", $_POST["tel"]);
            
            // Vérifie que c'est 10 chiffres et commence par 0
            if (strlen($tel) != 10) {
                $erreur["tel"] = "Numéro de tel incorrect (doit contenir 10 chiffres)";
            } elseif (!ctype_digit($tel)) {
                $erreur["tel"] = "Numéro de tel incorrect (uniquement des chiffres et espaces)";
            } elseif ($tel[0] != '0') {
                $erreur["tel"] = "Numéro de tel incorrect (doit commencer par 0)";
            }
        }
        // ADRESSE - strpos() est autorisé (dans la doc)
        if ($_POST["adresse"] == "") {
            $erreur["adresse"] = "Veuillez renseigner ce champ";
        } else {
            // Appel à l'API Adresse.data.gouv.fr
            $adresseEncoded = urlencode($_POST["adresse"]);
            $url = "https://api-adresse.data.gouv.fr/search/?q=" . $adresseEncoded . "&limit=1";
            
            // Lecture de l'API
            $response = file_get_contents($url);
            $data = json_decode($response, true);
            
            if ($data && isset($data['features']) && count($data['features']) > 0) {
                // L'adresse existe dans la base de données
                $score = $data['features'][0]['properties']['score'];
                if ($score < 0.5) {
                    $erreur["adresse"] = "Adresse non reconnue ou incomplète";
                }
            } else {
                $erreur["adresse"] = "Adresse non trouvée";
            }
        }
        // EMAIL - Validation sans filter_var()
        if ($_POST["mail"] == "") {
            $erreur["mail"] = "Veuillez renseigner ce champ";
        } 
        // MDP
        if ($_POST["mdp"] == "" || $_POST["mdpconfirme"] == "") {
            $erreur["mdp"] = "Veuillez renseigner ce champ";
        } elseif ($_POST["mdp"] != $_POST["mdpconfirme"]) {
            $erreur["mdp"] = "Les mots de passe ne correspondent pas";
            $erreur["mdpconfirme"] = "Les mots de passe ne correspondent pas";
        }
        // CGU
        if (!isset($_POST["cgu"])) {
            $erreur["cgu"] = "Veuillez cocher la case";
        }

        if (empty($erreur)) {
            // Ouverture du fichier en écriture (ajout à la fin)
            $infoclient = fopen("infoclient.txt", "a");
            
            // Construction de la ligne avec les infos séparées par des espaces
            // Format: civilite prenom nom anniv tel adresse mail mdp role
            $ligne = "";
            $ligne .= $_POST["civilite"] . " ";
            $ligne .= ucfirst(strtolower($_POST["prenom"])) . " ";
            $ligne .= $_POST["nom"] . " ";
            $ligne .= $_POST["anniv"] . " ";
            $ligne .= $tel . " ";
            $ligne .= $_POST["adresse"] . " ";
            $ligne .= $_POST["mail"] . " ";
            $ligne .= $_POST["mdp"] . " ";
            $ligne .= "client";
            
            // Écriture de la ligne dans le fichier
            fwrite($infoclient, $ligne);
            
            // Ajout d'un retour à la ligne
            fwrite($infoclient, "\n");
            
            // Fermeture du fichier
            fclose($infoclient);

            // Redirection vers la page de connexion
            header("Location: connexion.html");
            exit();
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
                <!-- PROFIL -->
                <div class="profil-menu">
                    <img src="images/Iconprofil.png" alt="Profil" class="icon">

                    <div class="profil-bulle">
                        <a href="inscription.html">Inscription</a>
                        <a href="connexion.html">Connexion</a>
                    </div>
                </div>

                <!-- PANIER -->
                <a href="">
                <img src="images/Iconpanier.png" alt="Panier" class="icon" id="panier">
                </a>
            </div>
        </header>

    <main>
        <h2>Créer un nouveau compte</h2>
        <form action="inscription.php" method="POST">
            <fieldset>

                <div class="civilite">
                    Civilité *
                    <input type="radio" id="civilite_mme" name="civilite" value="Mme" <?= (isset($_POST["civilite"]) && $_POST["civilite"]=="Mme") ? 'checked' : '' ?> > Mme
                    <input type="radio" id="civilite_m" name="civilite" value="M" <?= (isset($_POST["civilite"]) && $_POST["civilite"]=="M") ? 'checked' : '' ?>> M.<br>
                    <small><?= $erreur['civilite'] ?? '' ?></small>
                </div>

                <div class="champ">
                    Prénom *
                    <input type="text"  id= "prenom" name="prenom" maxlength=20 value="<?= isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : '' ?>" class="<?= isset($erreur['prenom']) ? 'erreur' : '' ?>" />
                    <small><?= $erreur['prenom'] ?? '' ?></small>
                </div>

                <div class="champ">
                    Nom *
                    <input type="text"  id="nom" name="nom" maxlength=20 value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>" class="<?= isset($erreur['nom']) ? 'erreur' : '' ?>" />
                    <small><?= $erreur['nom'] ?? '' ?></small>
                </div>

                <div class="champ">
                    Date de naissance *
                    <input type="date"  id="anniv" name="anniv" value="<?= isset($_POST['anniv']) ? htmlspecialchars($_POST['anniv']) : '' ?>" class="<?= isset($erreur['anniv']) ? 'erreur' : '' ?>" />
                    <small><?= $erreur['anniv'] ?? '' ?></small>
                </div>

                <div class="champ">
                    Téléphone
                    <input type="text"  id="tel" name="tel" placeholder="01 23 54 67 88" value="<?= isset($_POST['tel']) ? htmlspecialchars($_POST['tel']) : '' ?>" class="<?= isset($erreur['tel']) ? 'erreur' : '' ?>" />
                    <small><?= $erreur['tel'] ?? '' ?></small>
                </div>

                <div class="champ">
                    Adresse de livraison *
                    <input type="text" id="adresse" name="adresse" value="<?= isset($_POST['adresse']) ? htmlspecialchars($_POST['adresse']) : '' ?>" class="<?= isset($erreur['adresse']) ? 'erreur' : '' ?>" />
                    <small><?= $erreur['adresse'] ?? '' ?></small>
                </div>

                <div class="champ">
                    Votre adresse e-mail *
                    <input type="email" id="mail" name="mail" placeholder="nom@email.com" value="<?= isset($_POST['mail']) ? htmlspecialchars($_POST['mail']) : '' ?>" class="<?= isset($erreur['mail']) ? 'erreur' : '' ?>" />
                    <small><?= $erreur['mail'] ?? '' ?></small>
                </div>

                <div class="champ">
                    Créez votre mot de passe *
                    <input type="password"  id="mdp" name="mdp" maxlength=20 class="<?= isset($erreur['mdp']) ? 'erreur' : '' ?>" />
                    <small><?= $erreur['mdp'] ?? '' ?></small>
                    <img src="images/oeil.png" alt="Afficher mot de passe">
                </div>

                <div class="champ">
                    Confirmer le mot de passe *
                    <input type="password"  id="mdpconfirme" name="mdpconfirme" maxlength=20 class="<?= isset($erreur['mdpconfirme']) ? 'erreur' : '' ?>" />
                    <small><?= $erreur['mdpconfirme'] ?? '' ?></small>
                    <img src="images/oeil.png" alt="Afficher mot de passe">
                </div>

                <div class="conditions">
                    <input type="checkbox" id="cgu" name="cgu" <?= isset($_POST['cgu']) ? 'checked' : '' ?> />
                    J’accepte les <a href="conditions.html">conditions générales d’utilisation</a> * <br>
                    <small><?= $erreur['cgu'] ?? '' ?></small>
                </div>

                <input class="bouton" type="submit" value="CRÉER UN COMPTE"/>

                <p class="inscription">Vous avez déjà un compte ? <a class="lien" href="connexion.html">Se connecter</a></p>
                
            </fieldset>
        </form> 
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
