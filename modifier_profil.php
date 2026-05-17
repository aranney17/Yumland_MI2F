<?php
session_start();

// Vérifier si utilisateur connecté
if (!isset($_SESSION['id'])) {
    header("Location: connexion.php");
    exit();
}

// Charger JSON
$json = file_get_contents("data/infoclient.json");
$users = json_decode($json, true);

// Trouver l'utilisateur connecté
$id = $_SESSION['id'];
$userTrouve = null;

foreach ($users as $user) {
    if ($user['id'] == $id) {
        $userTrouve = $user;
        break;
    }
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
    <title>Modifier mes informations</title>

    <style>
        .message-succes { background: #d4edda; color: #155724; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; display: none; }
        .message-erreur { background: #f8d7da; color: #721c24; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; display: none; }
    </style>

</head>


<header>
        <div class="barres">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <h1><a href="accueil.php" class="logo">La Cour des Délices</a></h1>
    
            <div class="top-icons">
                <!-- PROFIL -->
                <div class="profil-menu">
                    <img src="images/Iconprofil.png" alt="Profil" class="icon">

                    <div class="profil-bulle">
                        <a href="logout.php">Déconnexion</a>
                    </div>
                </div>
        <a href="profil.php">← Retour</a>
</header>

<main>
<h2>Modifier mes informations</h2>

<!-- Messages de retour -->
    <p class="message-succes" id="msg-succes">✅ Vos informations ont bien été mises à jour !</p>
    <p class="message-erreur" id="msg-erreur">❌ Une erreur est survenue, veuillez réessayer.</p>

<?php if ($userTrouve): ?>
<form id="form-profil">
    <fieldset>

        <div class="civilite">
            Civilité *
            <label><input type="radio" name="civilite" value="Mme" <?= ($userTrouve['civilite'] ?? '') === 'Mme' ? 'checked' : '' ?>> Mme</label>
            <label><input type="radio" name="civilite" value="M." <?= ($userTrouve['civilite'] ?? '') === 'M.' ? 'checked' : '' ?>> M.</label>
        </div>

        <div class="champ">
            Prénom *
            <input type="text" name="prenom" value="<?= htmlspecialchars($userTrouve['prenom'] ?? '') ?>" />        
        </div>

        <div class="champ">
            Nom *
            <input type="text" name="nom" value="<?= htmlspecialchars($userTrouve['nom'] ?? '') ?>" />
        </div>

        <div class="champ">
            Date de naissance *
            <input type="date" name="anniv" value="<?= htmlspecialchars($userTrouve['date_naissance'] ?? '') ?>" />
        </div>

        <div class="champ">
            Téléphone
            <input type="text" name="tel" value="<?= htmlspecialchars($userTrouve['telephone'] ?? '') ?>" />
        </div>

        <div class="champ">
            Adresse de livraison *
            <input type="text" name="adresse" value="<?= htmlspecialchars($userTrouve['adresse'] ?? '') ?>" />
        </div>

        <div class="champ">
            Email *
            <input type="email" name="mail" value="<?= htmlspecialchars($userTrouve['mail'] ?? '') ?>" />
        </div>

        <!-- Bouton classique, intercepté par le JS -->
        <button class="bouton" type="button" id="btn-enregistrer">Enregistrer les modifications</button>
    </fieldset>
</form>
<?php else: ?>
<p>Utilisateur introuvable</p>
<?php endif; ?>
</main>

<script>
        document.getElementById('btn-enregistrer').addEventListener('click', function() {
    
            // Récupérer toutes les valeurs du formulaire
            const form = document.getElementById('form-profil');
            const formData = new FormData(form);
 
            // Envoi asynchrone vers profil_modifier.php
            fetch('profil_modifier.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const msgSucces = document.getElementById('msg-succes');
                const msgErreur = document.getElementById('msg-erreur');
 
                // Cacher les deux messages d'abord
                msgSucces.style.display = 'none';
                msgErreur.style.display = 'none';
 
                if (data.succes) {
                    // Afficher le message de succès sans recharger la page
                    msgSucces.style.display = 'block';
                } else {
                    // Afficher le message d'erreur retourné par le serveur
                    msgErreur.textContent = '❌ ' + (data.erreur ?? 'Une erreur est survenue.');
                    msgErreur.style.display = 'block';
                }
            })
            .catch(error => {
                // Erreur réseau ou autre
                const msgErreur = document.getElementById('msg-erreur');
                msgErreur.textContent = '❌ Impossible de contacter le serveur.';
                msgErreur.style.display = 'block';
            });
        });
    </script>

</body>
</html>
