<?php
// Charger les utilisateurs depuis le JSON
$json = file_get_contents("data/infoclient.json");
$utilisateurs = json_decode($json, true);

// Récupérer l'ID passé dans l'URL
$id = $_GET['id'] ?? null;

// Chercher l'utilisateur correspondant
$userTrouve = null;
foreach ($utilisateurs as $user) {
    if ($user['id'] == $id) {
        $userTrouve = $user;
        break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userTrouve) {
    $userTrouve['civilite'] = $_POST['civilite'] ?? $userTrouve['civilite'];
    $userTrouve['prenom'] = $_POST['prenom'] ?? $userTrouve['prenom'];
    $userTrouve['nom'] = $_POST['nom'] ?? $userTrouve['nom'];
    $userTrouve['date_naissance'] = $_POST['anniv'] ?? $userTrouve['date_naissance'];
    $userTrouve['telephone'] = $_POST['tel'] ?? $userTrouve['telephone'];
    $userTrouve['adresse'] = $_POST['adresse'] ?? $userTrouve['adresse'];
    $userTrouve['email'] = $_POST['mail'] ?? $userTrouve['email'];
    $userTrouve['statut'] = $_POST['statut'] ?? $userTrouve['statut'];
    $userTrouve['remise'] = $_POST['remise'] ?? $userTrouve['remise'];
    $userTrouve['bloque'] = isset($_POST['bloque']);

    // Mettre à jour dans le tableau
    foreach ($utilisateurs as $key => $u) {
        if ($u['id'] == $id) {
            $utilisateurs[$key] = $userTrouve;
            break;
        }
    }


    echo "<p style='color:green'>Modification enregistrée ! (phase 3)</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="connexion.css">
    <link rel="stylesheet" href="structg.css">
    <link rel="stylesheet" href="couleurs.css">
    <title>Modifier Utilisateur</title>
</head>


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
                        <a href="accueil.html">Déconnexion</a>
                    </div>
                </div>
        <a href="admin.php">← Retour à la liste</a>
</header>

<main>
<h2>Modifier Utilisateur</h2>
<?php if ($userTrouve): ?>
<form method="POST">
    <fieldset>

        <div class="civilite">
            Civilité *
            <label><input type="radio" name="civilite" value="Mme" <?= ($userTrouve['civilite'] ?? '') === 'Mme' ? 'checked' : '' ?>> Mme</label>
            <label><input type="radio" name="civilite" value="M." <?= ($userTrouve['civilite'] ?? '') === 'M.' ? 'checked' : '' ?>> M.</label>
        </div>

        <div class="champ">
            Prénom *
            <input type="text" name="prenom" value="<?= $userTrouve['prenom'] ?? '' ?>" />
        </div>

        <div class="champ">
            Nom *
            <input type="text" name="nom" value="<?= $userTrouve['nom'] ?? '' ?>" />
        </div>

        <div class="champ">
            Date de naissance *
            <input type="date" name="anniv" value="<?= $userTrouve['date_naissance'] ?? '' ?>" />
        </div>

        <div class="champ">
            Téléphone
            <input type="text" name="tel" value="<?= $userTrouve['telephone'] ?? '' ?>" />
        </div>

        <div class="champ">
            Adresse de livraison *
            <input type="text" name="adresse" value="<?= $userTrouve['adresse'] ?? '' ?>" />
        </div>

        <div class="champ">
            Email *
            <input type="email" name="mail" value="<?= $userTrouve['email'] ?? '' ?>" />
        </div>

        <!-- Champs admin -->
        <div class="champ">
            Statut
            <select name="statut">
                <?php
                $statuts = ['client','VIP','Premium','livreur','admin'];
                foreach($statuts as $statut):
                ?>
                <option value="<?= $statut ?>" <?= ($userTrouve['statut'] ?? '') === $statut ? 'selected' : '' ?>><?= $statut ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="champ">
            Remise (%)
            <input type="number" name="remise" value="<?= $userTrouve['remise'] ?? 0 ?>" min="0" max="100" />
        </div>

        <div>
            Compte bloqué
            <input type="checkbox" name="bloque" <?= ($userTrouve['bloque'] ?? false) ? 'checked' : '' ?> />
        </div>

        <input class="bouton" type="submit" value="Enregistrer les modifications"/>

    </fieldset>
</form>
<?php else: ?>
<p>Utilisateur introuvable</p>
<?php endif; ?>
</main>

</body>
</html>
