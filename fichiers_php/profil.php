<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../fichiers_php/connexion.php");
    exit();
}

$fichierClients   = "../data/infoclient.json";
$fichierCommandes = "../data/commande.json";

$users = json_decode(file_get_contents($fichierClients), true) ?? [];

/* User connecte + check bloque */
$id = $_SESSION['id'];
$userTrouve = null;
foreach ($users as $u) {
    if ($u['id'] == $id) { $userTrouve = $u; break; }
}
if (!$userTrouve) { die("Utilisateur introuvable."); }
if ($userTrouve['bloque'] ?? false) {
    session_destroy();
    die("Votre compte a été bloqué.");
}

/* Les deux seules civilites autorisees */
$CIVILITES = ['Mme', 'M.'];

if (isset($_POST['action']) && $_POST['action'] === 'update_profil') {
    header('Content-Type: application/json');

    $champ  = $_POST['champ']  ?? '';
    $valeur = trim($_POST['valeur'] ?? '');

    $champsAutorises = [
        'civilite', 'prenom', 'nom', 'date_naissance',
        'telephone', 'rue', 'code_postal', 'ville', 'mail'
    ];

    if (!in_array($champ, $champsAutorises)) {
        echo json_encode(['success' => false, 'erreur' => 'Champ non autorisé']);
        exit;
    }

    // Validation basique cote serveur
    if ($valeur === '') {
        echo json_encode(['success' => false, 'erreur' => 'Le champ ne peut pas être vide']);
        exit;
    }
    // CIVILITE : doit etre Mme ou M. (liste fermee)
    if ($champ === 'civilite' && !in_array($valeur, $CIVILITES, true)) {
        echo json_encode(['success' => false, 'erreur' => 'Civilité invalide (Mme ou M.)']);
        exit;
    }
    if ($champ === 'telephone' && !preg_match('/^\d{10}$/', str_replace(' ', '', $valeur))) {
        echo json_encode(['success' => false, 'erreur' => 'Téléphone invalide (10 chiffres)']);
        exit;
    }
    if ($champ === 'code_postal' && !preg_match('/^\d{5}$/', $valeur)) {
        echo json_encode(['success' => false, 'erreur' => 'Code postal invalide (5 chiffres)']);
        exit;
    }
    if ($champ === 'mail' && !filter_var($valeur, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'erreur' => 'Email invalide']);
        exit;
    }
    if ($champ === 'date_naissance' && $valeur > date('Y-m-d')) {
        echo json_encode(['success' => false, 'erreur' => 'Date invalide']);
        exit;
    }
    // Email unique
    if ($champ === 'mail') {
        foreach ($users as $u) {
            if ($u['id'] != $id && strtolower($u['mail']) === strtolower($valeur)) {
                echo json_encode(['success' => false, 'erreur' => 'Cet email est déjà utilisé']);
                exit;
            }
        }
    }

    // On garde l'ancien user pour retrouver les commandes
    $ancien = $userTrouve;

    // Update user
    foreach ($users as &$u) {
        if ($u['id'] == $id) {
            $u[$champ] = $valeur;
            if (in_array($champ, ['rue', 'code_postal', 'ville'])) {
                $u['adresse'] = ($u['rue'] ?? '') . ' '
                              . ($u['code_postal'] ?? '') . ' '
                              . ($u['ville'] ?? '');
            }
            $nouveau = $u;
            break;
        }
    }
    unset($u);
    file_put_contents($fichierClients, json_encode($users, JSON_PRETTY_PRINT));

    $statutsEnCours = ['a preparer', 'en preparation', 'commande préparée', 'en_livraison'];
    $commandes = json_decode(file_get_contents($fichierCommandes), true) ?? [];
    $modifCmd = false;
    foreach ($commandes as &$cmd) {
        $estDuUser = strtolower($cmd['nom']) === strtolower($ancien['nom'])
                  && strtolower($cmd['prenom']) === strtolower($ancien['prenom'])
                  && $cmd['telephone'] === $ancien['telephone'];

        if ($estDuUser && in_array($cmd['statut'], $statutsEnCours)) {
            $cmd['nom']       = $nouveau['nom'];
            $cmd['prenom']    = $nouveau['prenom'];
            $cmd['telephone'] = $nouveau['telephone'];
            $cmd['adresse']   = $nouveau['adresse'] ?? $cmd['adresse'];
            $modifCmd = true;
        }
    }
    unset($cmd);
    if ($modifCmd) {
        file_put_contents($fichierCommandes, json_encode($commandes, JSON_PRETTY_PRINT));
    }

    echo json_encode([
        'success'      => true,
        'valeur'       => $valeur,
        'cmd_majees'   => $modifCmd
    ]);
    exit;
}

$logoTarget = '../fichiers_php/accueil.php';
if      ($userTrouve['role'] === 'cuisinier')      $logoTarget = '../fichiers_php/commandes.php';
elseif  ($userTrouve['role'] === 'livreur')        $logoTarget = '../fichiers_php/livraison.php';
elseif  ($userTrouve['role'] === 'administrateur') $logoTarget = '../fichiers_php/administrateur.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil</title>
    <link rel="stylesheet" href="../fichiers_css/couleurs.css">
    <link rel="stylesheet" href="../fichiers_css/structg.css">
    <link rel="stylesheet" href="../fichiers_css/profil.css">
    <link rel="stylesheet" href="../fichiers_css/darkmode.css">
    
</head>
<body>

<?php include 'sidebar.php'; ?>

<header>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <nav class="sidebar" id="sidebar">
        <a href="#nouveautes">Nouveautés</a>
        <a href="../fichiers_php/presentation.php#viennoiseries">Viennoiseries</a>
        <a href="../fichiers_php/presentation.php#boissons">Boissons</a>
        <a href="../fichiers_php/presentation.php#gourmandises">Gourmandises</a>
        <a href="../fichiers_php/presentation.php#patisseries">Pâtisseries</a>
        <a href="../fichiers_php/presentation.php#gateaux">Gâteaux</a>
        <a href="../fichiers_php/presentation.php#tartes">Tartes</a>
        <a href="../fichiers_php/traiteur.php">Commande traiteur</a>
    </nav>
    <div class="barres"><span></span><span></span><span></span></div>
    <h1><a href="<?= $logoTarget ?>" class="logo">La Cour des Délices</a></h1>
    <div class="top-icons">
        <div class="profil-menu">
            <img src="../images/Iconprofil.png" class="icon">
            <div class="profil-bulle">
                <a href="../fichiers_php/profil.php">Profil</a>
                <a href="../fichiers_php/logout.php">Déconnexion</a>
            </div>
        </div>
    </div>
</header>

<main class="container">

<aside class="sidebar-profil">
    <ul class="menu">
        <li><a href="../fichiers_php/profil.php"><strong>Informations</strong></a></li>
        <?php if ($userTrouve['role'] === 'client'): ?>
            <li><a href="../fichiers_php/profil2.php">Historique de commandes</a></li>
        <?php endif; ?>
    </ul>
    <br>
    <a href="../fichiers_php/logout.php"><p class="logout">Déconnexion</p></a>
</aside>

<section class="informations">
    <h2 class="title">Informations</h2>


    <?php
    /* Champ editable TEXTE / DATE / EMAIL */
    function champEditable($cle, $label, $valeur, $type = 'text') {
        $val = htmlspecialchars($valeur ?? '');
        echo <<<HTML
        <div class="block" data-champ="{$cle}" data-type="{$type}">
            <span class="label-champ">{$label} :</span>
            <span class="value-champ">{$val}</span>
            <div class="actions-edit">
                <button class="btn-edit" type="button">✎ Modifier</button>
                <input type="{$type}" class="input-edit" style="display:none" value="{$val}">
                <button class="btn-save"   type="button" style="display:none">✓ Valider</button>
                <button class="btn-cancel" type="button" style="display:none">✗ Annuler</button>
                <span class="msg-confirm">✓ enregistré</span>
                <span class="msg-erreur"></span>
            </div>
        </div>
        HTML;
    }

    /* Champ editable LISTE DEROULANTE (ex: civilite Mme/M.) */
    function champEditableListe($cle, $label, $valeur, $options) {
        $val = htmlspecialchars($valeur ?? '');
        $optionsHtml = '';
        foreach ($options as $opt) {
            $optEsc = htmlspecialchars($opt);
            $sel = ($opt === $valeur) ? 'selected' : '';
            $optionsHtml .= "<option value=\"{$optEsc}\" {$sel}>{$optEsc}</option>";
        }
        echo <<<HTML
        <div class="block" data-champ="{$cle}" data-type="select">
            <span class="label-champ">{$label} :</span>
            <span class="value-champ">{$val}</span>
            <div class="actions-edit">
                <button class="btn-edit" type="button">✎ Modifier</button>
                <select class="input-edit" style="display:none">{$optionsHtml}</select>
                <button class="btn-save"   type="button" style="display:none">✓ Valider</button>
                <button class="btn-cancel" type="button" style="display:none">✗ Annuler</button>
                <span class="msg-confirm">✓ enregistré</span>
                <span class="msg-erreur"></span>
            </div>
        </div>
        HTML;
    }

    // Civilite : liste deroulante (2 choix seulement)
    champEditableListe('civilite', 'Civilité', $userTrouve['civilite'] ?? '', $CIVILITES);

    champEditable('prenom',          'Prénom',            $userTrouve['prenom']          ?? '');
    champEditable('nom',             'Nom',               $userTrouve['nom']             ?? '');
    champEditable('date_naissance',  'Date de naissance', $userTrouve['date_naissance']  ?? '', 'date');
    champEditable('telephone',       'Téléphone',         $userTrouve['telephone']       ?? '');
    champEditable('rue',             'Rue',               $userTrouve['rue']             ?? '');
    champEditable('code_postal',     'Code postal',       $userTrouve['code_postal']     ?? '');
    champEditable('ville',           'Ville',             $userTrouve['ville']           ?? '');
    champEditable('mail',            'Adresse mail',      $userTrouve['mail']            ?? '', 'email');
    ?>

    <div class="block">
        <span class="label-champ">Rôle :</span>
        <span class="value-champ"><?= htmlspecialchars($userTrouve['role']) ?></span>
        <small><em>(non modifiable)</em></small>
    </div>

    <div class="block">
        <span class="label-champ">Mot de passe :</span>
        <span class="value-champ">*********</span>
        <a href="../fichiers_php/modifier_profil.php">Changer mon mot de passe</a>
    </div>
</section>
</main>

<footer>
    <p>Suivez nous sur nos réseaux!<br>
        <img src="../images/Iconinstagram.jpg" class="icon">
        <img src="../images/Icontiktok.jpg" class="icon">
        <img src="../images/Icontwitter.png" class="icon">
    </p>
    <div class="infos-footer">
        <div class="info"><img src="../images/Iconlocalisation.png" class="icon"><span>5 av de la république, 75300 Paris</span></div>
        <div class="info"><img src="../images/Iconhorloge.png" class="icon"><span>Tous les jours 9h - 22h</span></div>
    </div>
    <h5>© 2026 Pâtisserie</h5>
</footer>


<script>
document.querySelectorAll('.block[data-champ]').forEach(function(bloc) {

    const valueSpan = bloc.querySelector('.value-champ');
    const input     = bloc.querySelector('.input-edit'); // input OU select
    const btnEdit   = bloc.querySelector('.btn-edit');
    const btnSave   = bloc.querySelector('.btn-save');
    const btnCancel = bloc.querySelector('.btn-cancel');
    const msgOk     = bloc.querySelector('.msg-confirm');
    const msgKo     = bloc.querySelector('.msg-erreur');

    function modeEdition() {
        // Pour un select comme pour un input, .value fonctionne
        input.value = valueSpan.textContent.trim();
        valueSpan.style.display = 'none';
        btnEdit.style.display = 'none';
        input.style.display = '';
        btnSave.style.display = '';
        btnCancel.style.display = '';
        msgOk.style.display = 'none';
        msgKo.style.display = 'none';
        input.focus();
    }

    function modeAffichage() {
        valueSpan.style.display = '';
        btnEdit.style.display = '';
        input.style.display = 'none';
        btnSave.style.display = 'none';
        btnCancel.style.display = 'none';
    }

    btnEdit.addEventListener('click', modeEdition);
    btnCancel.addEventListener('click', modeAffichage);

    btnSave.addEventListener('click', function() {
        const champ  = bloc.dataset.champ;
        const valeur = input.value;

        const formData = new FormData();
        formData.append('action', 'update_profil');
        formData.append('champ',  champ);
        formData.append('valeur', valeur);

        fetch('../fichiers_php/profil.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    valueSpan.textContent = data.valeur;
                    modeAffichage();
                    msgOk.textContent = data.cmd_majees
                        ? '✓ enregistré (commandes en cours mises à jour)'
                        : '✓ enregistré';
                    msgOk.style.display = '';
                    setTimeout(() => { msgOk.style.display = 'none'; }, 3000);
                } else {
                    msgKo.textContent = '✗ ' + (data.erreur || 'Erreur');
                    msgKo.style.display = '';
                }
            })
            .catch(err => {
                msgKo.textContent = '✗ Erreur réseau';
                msgKo.style.display = '';
            });
    });
});
</script>

<button id="btn-darkmode" class="btn-darkmode">☾</button>
<script src="../fichiers_js/darkmode.js"></script>
</body>
</html>
