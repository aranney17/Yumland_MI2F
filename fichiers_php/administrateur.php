<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: fichiers_php/connexion.php");
    exit();
}

$fichierClients   = "data/infoclient.json";
$fichierCommandes = "data/commande.json";

$utilisateurs = json_decode(file_get_contents($fichierClients), true) ?? [];

//securisation
$roleConnecte = null;
$bloqueConnecte = false;
foreach ($utilisateurs as $u) {
    if ($u['id'] == $_SESSION['id']) {
        $roleConnecte = $u['role'];
        $bloqueConnecte = $u['bloque'] ?? false;
        break;
    }
}
if ($bloqueConnecte) {
    session_destroy();
    die("Votre compte a été bloqué. <a href='fichiers_php/connexion.php'>Retour</a>");
}
if ($roleConnecte !== 'administrateur') {
    http_response_code(403);
    die("Accès refusé. Cette page est réservée aux administrateurs.");
}

$ROLES_VALIDES = ['client', 'livreur', 'cuisinier', 'administrateur'];

if (isset($_POST['action']) && $_POST['action'] === 'toggle_bloque') {
    header('Content-Type: application/json');
    $idCible = (int) $_POST['user_id'];

    $userCible = null; $nouveauBloque = null;
    foreach ($utilisateurs as &$u) {
        if ($u['id'] === $idCible) {
            if ($u['id'] == $_SESSION['id']) {
                echo json_encode(['success' => false, 'erreur' => 'Vous ne pouvez pas vous bloquer vous-meme']);
                exit;
            }
            $u['bloque'] = !($u['bloque'] ?? false);
            $nouveauBloque = $u['bloque'];
            $userCible = $u;
            break;
        }
    }
    unset($u);

    if (!$userCible) {
        echo json_encode(['success' => false, 'erreur' => 'Utilisateur introuvable']);
        exit;
    }

    file_put_contents($fichierClients, json_encode($utilisateurs, JSON_PRETTY_PRINT));

    if ($nouveauBloque) {
        $commandes = json_decode(file_get_contents($fichierCommandes), true) ?? [];
        $nouvellesCmd = [];
        foreach ($commandes as $cmd) {
            $estDuUser = strtolower($cmd['nom']) === strtolower($userCible['nom'])
                      && strtolower($cmd['prenom']) === strtolower($userCible['prenom'])
                      && $cmd['telephone'] === $userCible['telephone'];
            if (!$estDuUser) { $nouvellesCmd[] = $cmd; }
        }
        file_put_contents($fichierCommandes, json_encode($nouvellesCmd, JSON_PRETTY_PRINT));
    }

    echo json_encode(['success' => true, 'bloque' => $nouveauBloque]);
    exit;
}

//change le role d'utilisateur
if (isset($_POST['action']) && $_POST['action'] === 'changer_role') {
    header('Content-Type: application/json');

    $idCible      = (int)    $_POST['user_id'];
    $nouveauRole  = (string) $_POST['role'];

    // Protections
    if (!in_array($nouveauRole, $ROLES_VALIDES, true)) {
        echo json_encode(['success' => false, 'erreur' => 'Rôle invalide']);
        exit;
    }
    if ($idCible === (int)$_SESSION['id']) {
        echo json_encode(['success' => false, 'erreur' => 'Vous ne pouvez pas modifier votre propre rôle']);
        exit;
    }

    $trouve = false;
    foreach ($utilisateurs as &$u) {
        if ($u['id'] === $idCible) {
            $u['role'] = $nouveauRole;
            $trouve = true;
            break;
        }
    }
    unset($u);

    if (!$trouve) {
        echo json_encode(['success' => false, 'erreur' => 'Utilisateur introuvable']);
        exit;
    }

    file_put_contents($fichierClients, json_encode($utilisateurs, JSON_PRETTY_PRINT));

    echo json_encode(['success' => true, 'role' => $nouveauRole]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrateur</title>
    <link rel="stylesheet" href="fichiers_css/structg.css">
    <link rel="stylesheet" href="fichiers_css/couleurs.css">
    <link rel="stylesheet" href="fichiers_css/administrateur.css">
    <link rel="stylesheet" href="fichiers_css/darkmode.css">
    <link rel="stylesheet" href="fichiers_css/darkmode_admin.css"> 
</head>
<body>

<header>
    <div class="barres"><span></span><span></span><span></span></div>
    <h1><a href="fichiers_php/administrateur.php" class="logo">La Cour des Délices</a></h1>
    <div class="top-icons">
        <a href="fichiers_php/profil.php"><img src="images/Iconprofil.png" alt="Profil" class="icon"></a>
        <a href="fichiers_php/logout.php"><p class="deconnexion">déconnexion</p></a>
    </div>
</header>

<div class="search-bar">
    <input type="search" placeholder="Chercher un utilisateur">
    <button><img src="images/Iconloupe.png" alt="loupe"></button>
</div>

<nav class="menu-horizontal">
    <ul>
        <li><a href="fichiers_php/administrateur.php" class="active">Utilisateurs</a></li>
        <li><a href="fichiers_php/administrateur2.php">Commandes</a></li>
    </ul>
</nav>

<main>
    <button class="filtre">Filtrer <img src="images/filter.png"></button>
    <section>
        <table>
            <tr>
                <th>Nom</th><th>Prénom</th><th>Email</th><th>Rôle</th>
                <th>Commandes</th><th>Remise</th><th>Actions</th>
            </tr>

            <?php foreach ($utilisateurs as $user):
                $estMoi = ($user['id'] == $_SESSION['id']);
            ?>
            <tr class="ligne <?= ($user['bloque'] ?? false) ? 'ligne-bloque' : '' ?>" id="ligne-<?= $user['id'] ?>">
                <td><a href="fichiers_php/profil_admin.php?id=<?= $user['id'] ?>"><?= htmlspecialchars($user['nom']) ?></a></td>
                <td><?= htmlspecialchars($user['prenom']) ?></td>
                <td><?= htmlspecialchars($user['mail']) ?></td>

                <td>
                    <select class="select-role"
                            data-id="<?= $user['id'] ?>"
                            data-ancien="<?= htmlspecialchars($user['role']) ?>"
                            <?= $estMoi ? 'disabled title="Vous ne pouvez pas modifier votre propre rôle"' : '' ?>
                            onchange="changerRole(this)">
                        <?php foreach ($ROLES_VALIDES as $r): ?>
                            <option value="<?= $r ?>" <?= ($user['role'] === $r) ? 'selected' : '' ?>>
                                <?= ucfirst($r) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="feedback-role" id="feedback-<?= $user['id'] ?>"></span>
                </td>

                <td><?= $user['commandes'] ?? 0 ?></td>
                <td><?= $user['remise'] ?? 0 ?>%</td>
                <td>
                    <?php if ($estMoi): ?>
                        <span style="font-style:italic;font-size:12px;">(vous)</span>
                    <?php else: ?>
                        <button class="filtre btn-bloquer <?= ($user['bloque'] ?? false) ? 'est-bloque' : '' ?>"
                                data-id="<?= $user['id'] ?>"
                                onclick="toggleBloque(this)">
                            <?= ($user['bloque'] ?? false) ? 'Débloquer' : 'Bloquer' ?>
                        </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>
</main>

<footer>
    <p>suivez nous sur nos réseaux!<br>
        <img src="images/Iconinstagram.jpg" class="icon">
        <img src="images/Icontiktok.jpg" class="icon">
        <img src="images/Icontwitter.png" class="icon">
    </p>
    <div class="infos-footer">
        <div class="info"><img src="images/Iconlocalisation.png" class="icon"><span>5 av de la république, 75300 Paris</span></div>
        <div class="info"><img src="images/Iconhorloge.png" class="icon"><span>Tous les jours 9h - 22h</span></div>
    </div>
    <h5>© 2026 Pâtisserie</h5>
</footer>

<script>
function toggleBloque(btn) {
    const userId = btn.dataset.id;
    const confirmation = btn.classList.contains('est-bloque')
        ? 'Confirmer le déblocage de cet utilisateur ?'
        : 'Confirmer le BLOCAGE ? Toutes ses commandes seront supprimées.';
    if (!confirm(confirmation)) return;

    const formData = new FormData();
    formData.append('action', 'toggle_bloque');
    formData.append('user_id', userId);

    fetch('administrateur.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.textContent = data.bloque ? 'Débloquer' : 'Bloquer';
                btn.classList.toggle('est-bloque', data.bloque);
                const ligne = document.getElementById('ligne-' + userId);
                if (ligne) ligne.classList.toggle('ligne-bloque', data.bloque);
            } else {
                alert(data.erreur || 'Erreur inconnue');
            }
        });
}

function changerRole(select) {
    const userId      = select.dataset.id;
    const ancienRole  = select.dataset.ancien;
    const nouveauRole = select.value;

    if (nouveauRole === ancienRole) return; // pas de changement

    const ok = confirm("Changer le rôle vers \"" + nouveauRole + "\" ?\n\n"
        + "Si l'utilisateur est connecté, il sera renvoyé hors de sa page actuelle "
        + "à sa prochaine action.");
    if (!ok) {
        // Revenir a l'ancienne valeur
        select.value = ancienRole;
        return;
    }

    const formData = new FormData();
    formData.append('action', 'changer_role');
    formData.append('user_id', userId);
    formData.append('role', nouveauRole);

    const feedback = document.getElementById('feedback-' + userId);

    fetch('fichiers_php/administrateur.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                select.dataset.ancien = data.role; // memoriser le nouveau
                feedback.textContent = '✓ Rôle modifié';
                feedback.classList.remove('erreur');
                feedback.classList.add('show');
                setTimeout(() => feedback.classList.remove('show'), 2500);
            } else {
                // Revenir a l'ancienne valeur en cas d'echec
                select.value = ancienRole;
                feedback.textContent = '✗ ' + (data.erreur || 'Erreur');
                feedback.classList.add('erreur', 'show');
                setTimeout(() => feedback.classList.remove('show'), 3000);
            }
        })
        .catch(err => {
            select.value = ancienRole;
            alert("Erreur réseau : " + err);
        });
}
</script>

<button id="btn-darkmode" class="btn-darkmode">☾</button>
<script src="fichiers_js/darkmode.js"></script>
</body>
</html>
