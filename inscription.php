<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="connexion.css">
    <link rel="stylesheet" href="structg.css">
    <link rel="stylesheet" href="couleurs.css">
    <link rel="stylesheet" href="darkmode.css">
    <title>Inscription</title>
</head>
<body>

<?php
$erreur = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // CIVILITE
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
    // TELEPHONE
    if ($_POST["tel"] == "") {
        $erreur["tel"] = "Veuillez renseigner ce champ";
    } else {
        $tel = str_replace(" ", "", $_POST["tel"]);
        if (strlen($tel) != 10) {
            $erreur["tel"] = "Numéro de tel incorrect (10 chiffres)";
        } elseif (!ctype_digit($tel)) {
            $erreur["tel"] = "Uniquement des chiffres";
        } elseif ($tel[0] != '0') {
            $erreur["tel"] = "Doit commencer par 0";
        }
    }

    /* -------------------------------------------------------------
       ADRESSE - separee en 3 champs : rue / code postal / ville
    ------------------------------------------------------------- */
    // Numero + nom de rue
    if (empty($_POST["rue"])) {
        $erreur["rue"] = "Veuillez renseigner ce champ";
    } else {
        $rue = trim($_POST["rue"]);
        // Doit contenir au moins un chiffre (numero) et un mot
        $aUnNumero = preg_match('/\d/', $rue);
        $typesVoie = ["rue", "avenue", "boulevard", "place", "impasse",
                      "allée", "allee", "chemin", "route", "cours"];
        $aUneVoie = false;
        $rueLower = strtolower($rue);
        foreach ($typesVoie as $voie) {
            if (strpos($rueLower, $voie) !== false) { $aUneVoie = true; break; }
        }
        if (!$aUnNumero)        $erreur["rue"] = "Doit contenir un numéro de rue";
        elseif (!$aUneVoie)     $erreur["rue"] = "Doit contenir un type de voie (rue, avenue...)";
        elseif (strlen($rue) < 5) $erreur["rue"] = "Adresse trop courte";
    }

    // Code postal
    if (empty($_POST["code_postal"])) {
        $erreur["code_postal"] = "Veuillez renseigner ce champ";
    } else {
        $cp = trim($_POST["code_postal"]);
        if (!ctype_digit($cp) || strlen($cp) != 5) {
            $erreur["code_postal"] = "5 chiffres requis";
        } else {
            $cpInt = intval($cp);
            if ($cpInt < 1000 || $cpInt > 95999) {
                $erreur["code_postal"] = "Code postal français invalide (01xxx à 95xxx)";
            }
        }
    }

    // Ville
    if (empty($_POST["ville"])) {
        $erreur["ville"] = "Veuillez renseigner ce champ";
    } else {
        $ville = trim($_POST["ville"]);
        // Lettres, espaces, tirets, apostrophes uniquement
        if (!preg_match("/^[a-zA-ZÀ-ÿ\s\-']+$/", $ville)) {
            $erreur["ville"] = "Caractères invalides dans la ville";
        }
    }

    // EMAIL - format + UNICITE
    if ($_POST["mail"] == "") {
        $erreur["mail"] = "Veuillez renseigner ce champ";
    } else {
        $mailSaisi = trim(strtolower($_POST["mail"]));
        // Verifier que l'email n'est pas deja utilise
        $fichier = "data/infoclient.json";
        if (file_exists($fichier)) {
            $existants = json_decode(file_get_contents($fichier), true) ?? [];
            foreach ($existants as $u) {
                if (strtolower($u["mail"]) === $mailSaisi) {
                    $erreur["mail"] = "Cette adresse mail est déjà utilisée";
                    break;
                }
            }
        }
    }

    // MDP
    if ($_POST["mdp"] == "" || $_POST["mdpconfirme"] == "") {
        $erreur["mdp"] = "Veuillez renseigner ce champ";
    } elseif ($_POST["mdp"] != $_POST["mdpconfirme"]) {
        $erreur["mdp"]         = "Les mots de passe ne correspondent pas";
        $erreur["mdpconfirme"] = "Les mots de passe ne correspondent pas";
    }
    // CGU
    if (!isset($_POST["cgu"])) {
        $erreur["cgu"] = "Veuillez cocher la case";
    }

    /* -------------------------------------------------------------
       Si tout est OK : enregistrer le nouvel utilisateur
    ------------------------------------------------------------- */
    if (empty($erreur)) {

        function nettoyerChamp($valeur) {
            return str_replace(",", " ", $valeur);
        }

        $fichier = "data/infoclient.json";
        $utilisateurs = file_exists($fichier)
            ? (json_decode(file_get_contents($fichier), true) ?? [])
            : [];

        $tel = str_replace(" ", "", $_POST["tel"]);
        $mdp_hash = password_hash($_POST["mdp"], PASSWORD_DEFAULT);

        // ID propre : max + 1 (evite les doublons si suppression)
        $maxId = 0;
        foreach ($utilisateurs as $u) {
            if ($u["id"] > $maxId) $maxId = $u["id"];
        }
        $nouvelId = $maxId + 1;

        // Reconstituer une adresse complete pour la compatibilite
        // avec les pages qui utilisent encore $user['adresse']
        $rue         = nettoyerChamp($_POST["rue"]);
        $code_postal = trim($_POST["code_postal"]);
        $ville       = nettoyerChamp(ucwords(strtolower($_POST["ville"])));
        $adresseComplete = $rue . " " . $code_postal . " " . $ville;

        $nouvelUtilisateur = [
            "id"               => $nouvelId,
            "civilite"         => $_POST["civilite"],
            "prenom"           => nettoyerChamp(ucfirst(strtolower($_POST["prenom"]))),
            "nom"              => nettoyerChamp(strtoupper($_POST["nom"])),
            "date_naissance"   => $_POST["anniv"],
            "telephone"        => $tel,
            // Nouveaux champs separes :
            "rue"              => $rue,
            "code_postal"      => $code_postal,
            "ville"            => $ville,
            // Champ concatene pour compatibilite avec l'existant :
            "adresse"          => $adresseComplete,
            "mail"             => strtolower(trim($_POST["mail"])),
            "mdp"              => $mdp_hash,
            "role"             => "client",
            "commandes"        => 0,
            "bloque"           => false,
            "remise"           => 0,
            "dateinscription"  => date("Y-m-d"),
            "dateconnexion"    => null
        ];

        $utilisateurs[] = $nouvelUtilisateur;
        file_put_contents($fichier, json_encode($utilisateurs, JSON_PRETTY_PRINT));

        // Redirection avec flag pour afficher message de succes
        header("Location: connexion.php?inscription=ok");
        exit();
    }
}
?>

<header>
    <div class="barres"><span></span><span></span><span></span></div>
    <h1><a href="accueil.php" class="logo">La Cour des Délices</a></h1>
    <div class="top-icons">
        <div class="profil-menu">
            <img src="images/Iconprofil.png" alt="Profil" class="icon">
            <div class="profil-bulle">
                <a href="inscription.php">Inscription</a>
                <a href="connexion.php">Connexion</a>
            </div>
        </div>
        <a href=""><img src="images/Iconpanier.png" alt="Panier" class="icon" id="panier"></a>
    </div>
</header>

<main>
    <h2>Créer un nouveau compte</h2>
    <form action="inscription.php" method="POST">
        <fieldset>

            <div class="civilite">
                Civilité *
                <input type="radio" name="civilite" value="Mme" <?= (isset($_POST["civilite"]) && $_POST["civilite"]=="Mme") ? 'checked' : '' ?>> Mme
                <input type="radio" name="civilite" value="M" <?= (isset($_POST["civilite"]) && $_POST["civilite"]=="M") ? 'checked' : '' ?>> M.<br>
                <small><?= $erreur['civilite'] ?? '' ?></small>
            </div>

            <div class="champ">
                Prénom *
                <input type="text" name="prenom" maxlength="20" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" class="<?= isset($erreur['prenom']) ? 'erreur' : '' ?>" />
                <small><?= $erreur['prenom'] ?? '' ?></small>
            </div>

            <div class="champ">
                Nom *
                <input type="text" name="nom" maxlength="20" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" class="<?= isset($erreur['nom']) ? 'erreur' : '' ?>" />
                <small><?= $erreur['nom'] ?? '' ?></small>
            </div>

            <div class="champ">
                Date de naissance *
                <input type="date" name="anniv" value="<?= htmlspecialchars($_POST['anniv'] ?? '') ?>" class="<?= isset($erreur['anniv']) ? 'erreur' : '' ?>" />
                <small><?= $erreur['anniv'] ?? '' ?></small>
            </div>

            <div class="champ">
                Téléphone *
                <input type="text" name="tel" placeholder="01 23 54 67 88" value="<?= htmlspecialchars($_POST['tel'] ?? '') ?>" class="<?= isset($erreur['tel']) ? 'erreur' : '' ?>" />
                <small><?= $erreur['tel'] ?? '' ?></small>
            </div>

            <!-- ADRESSE EN 3 CHAMPS -->
            <div class="champ">
                Numéro et nom de rue *
                <input type="text" name="rue" placeholder="57 avenue Victor Hugo" value="<?= htmlspecialchars($_POST['rue'] ?? '') ?>" class="<?= isset($erreur['rue']) ? 'erreur' : '' ?>" />
                <small><?= $erreur['rue'] ?? '' ?></small>
            </div>

            <div class="champ">
                Code postal *
                <input type="text" name="code_postal" placeholder="75116" maxlength="5" value="<?= htmlspecialchars($_POST['code_postal'] ?? '') ?>" class="<?= isset($erreur['code_postal']) ? 'erreur' : '' ?>" />
                <small><?= $erreur['code_postal'] ?? '' ?></small>
            </div>

            <div class="champ">
                Ville *
                <input type="text" name="ville" placeholder="Paris" value="<?= htmlspecialchars($_POST['ville'] ?? '') ?>" class="<?= isset($erreur['ville']) ? 'erreur' : '' ?>" />
                <small><?= $erreur['ville'] ?? '' ?></small>
            </div>

            <div class="champ">
                Votre adresse e-mail *
                <input type="email" name="mail" placeholder="nom@email.com" value="<?= htmlspecialchars($_POST['mail'] ?? '') ?>" class="<?= isset($erreur['mail']) ? 'erreur' : '' ?>" />
                <small><?= $erreur['mail'] ?? '' ?></small>
            </div>

            <div class="champ">
                Créez votre mot de passe *
                <input type="password" name="mdp" maxlength="20" class="<?= isset($erreur['mdp']) ? 'erreur' : '' ?>" />
                <small><?= $erreur['mdp'] ?? '' ?></small>
            </div>

            <div class="champ">
                Confirmer le mot de passe *
                <input type="password" name="mdpconfirme" maxlength="20" class="<?= isset($erreur['mdpconfirme']) ? 'erreur' : '' ?>" />
                <small><?= $erreur['mdpconfirme'] ?? '' ?></small>
            </div>

            <div class="conditions">
                <input type="checkbox" name="cgu" <?= isset($_POST['cgu']) ? 'checked' : '' ?> />
                J'accepte les <a href="conditions.html">CGU</a> *<br>
                <small><?= $erreur['cgu'] ?? '' ?></small>
            </div>

            <input class="bouton" type="submit" value="CRÉER UN COMPTE"/>
            <p class="inscription">Vous avez déjà un compte ? <a class="lien" href="connexion.php">Se connecter</a></p>
        </fieldset>
    </form>
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
