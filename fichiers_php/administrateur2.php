<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: connexion.php");
    exit();
}

$utilisateurs = json_decode(file_get_contents("data/infoclient.json"), true) ?? [];

/* SECURITE admin + check bloque */
$role = null;
$bloque = false;
foreach ($utilisateurs as $u) {
    if ($u['id'] == $_SESSION['id']) {
        $role = $u['role'];
        $bloque = $u['bloque'] ?? false;
        break;
    }
}
if ($bloque) {
    session_destroy();
    die("Votre compte a été bloqué. <a href='connexion.php'>Retour</a>");
}
if ($role !== 'administrateur') {
    http_response_code(403);
    die("Accès refusé. Cette page est réservée aux administrateurs.");
}

$commandes = json_decode(file_get_contents("data/commande.json"), true) ?? [];
$notations = file_exists("data/notation.json")
    ? (json_decode(file_get_contents("data/notation.json"), true) ?? [])
    : [];


function normaliserRef($s) {
    return strtoupper(trim((string) $s));
}

function trouverNotation($reference, $notations) {
    $refCible = normaliserRef($reference);
    foreach ($notations as $n) {
        if (!isset($n['reference'])) continue;
        if (normaliserRef($n['reference']) === $refCible) return $n;
    }
    return null;
}

function etoiles($note) {
    $note = (int) $note;
    if ($note < 0) $note = 0;
    if ($note > 5) $note = 5;
    return str_repeat('★', $note) . str_repeat('☆', 5 - $note);
}

$debugMode = isset($_GET['debug']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administrateur - Commandes</title>
    <link rel="stylesheet" href="structg.css">
    <link rel="stylesheet" href="couleurs.css">
    <link rel="stylesheet" href="administrateur2.css">
    <link rel="stylesheet" href="darkmode.css">
    <link rel="stylesheet" href="darkmode_admin.css">
</head>
<body>

<header>
    <div class="barres"><span></span><span></span><span></span></div>
    <h1><a href="administrateur.php" class="logo">La Cour des Délices</a></h1>
    <div class="top-icons">
        <a href="profil.php"><img src="images/Iconprofil.png" alt="Profil" class="icon"></a>
        <a href="logout.php"><p class="deconnexion">déconnexion</p></a>
    </div>
</header>

<div class="search-bar">
    <input type="search" placeholder="Chercher une commande">
    <button><img src="images/Iconloupe.png" alt="loupe"></button>
</div>

<nav class="menu-horizontal">
    <ul>
        <li><a href="administrateur.php">Utilisateurs</a></li>
        <li><a href="administrateur2.php" class="active">Commandes</a></li>
    </ul>
</nav>

<?php if ($debugMode): ?>
<div class="debug-box">
    <h3>🔍 Mode debug (ajoute <code>?debug=1</code> dans l'URL pour activer)</h3>

    <p><strong>Références dans <code>commande.json</code> :</strong></p>
    <pre><?php
    foreach ($commandes as $c) {
        echo "  [" . $c['id'] . "] " . var_export($c['reference'], true) . "  (normalisée : " . normaliserRef($c['reference']) . ")\n";
    }
    ?></pre>

    <p><strong>Références dans <code>notation.json</code> :</strong></p>
    <pre><?php
    foreach ($notations as $n) {
        echo "  " . var_export($n['reference'] ?? '?', true) . "  (normalisée : " . normaliserRef($n['reference'] ?? '') . ")\n";
    }
    ?></pre>

    <p>Si les références "normalisées" sont identiques mais pas affichées,
       il y a un problème ailleurs. Si elles diffèrent, c'est là qu'il faut chercher.</p>
</div>
<?php endif; ?>

<main>
    <button class="filtre">Filtrer <img src="images/filter.png"></button>
    <section>
        <table>
            <tr>
                <th>Date</th><th>Référence</th><th>Client</th>
                <th>Montant</th><th>Paiement</th><th>Statut</th><th>Détails</th>
            </tr>

            <?php foreach ($commandes as $commande):
                $notation = trouverNotation($commande['reference'], $notations);
            ?>
            <tr class="ligne">
                <td><?= date("d M Y", strtotime($commande['date'])) ?></td>
                <td><?= htmlspecialchars($commande['reference']) ?></td>
                <td><?= htmlspecialchars($commande['prenom'] . ' ' . $commande['nom']) ?></td>
                <td><?= number_format($commande['montant'], 2, ',', ' ') ?> €</td>
                <td><span class="case payee"><?= strtoupper($commande['paiement']) ?></span></td>
                <td>
                    <span class="case <?= preg_replace('/[^a-z]/i','_', $commande['statut']) ?>">
                        <?= strtoupper($commande['statut']) ?>
                    </span>
                    <?php if ($notation): ?>
                        <span title="Cette commande a été évaluée">⭐</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button class="bouton-details" data-cible="details-<?= $commande['id'] ?>">
                        <img src="images/iconfleche.jpg" alt="Détails" class="fleche">
                    </button>
                </td>
            </tr>

            <tr id="details-<?= $commande['id'] ?>" class="ligne-details">
                <td colspan="7">

                    <table class="table-details">
                        <tr><th>Produit</th><th>Quantité</th><th>Prix</th></tr>
                        <?php foreach ($commande['produits'] as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['produit'] ?? '—') ?></td>
                                <td><?= $p['quantite'] ?></td>
                                <td><?= $p['prix'] ?> €</td>
                            </tr>
                        <?php endforeach; ?>
                    </table>

                    <?php if ($notation): ?>
                        <div class="bloc-notation">
                            <h4>Évaluation client</h4>

                            <div class="ligne-note">
                                <strong>Livraison :</strong>
                                <span class="etoiles"><?= etoiles($notation['livraison']['note'] ?? 0) ?></span>
                                (<?= $notation['livraison']['note'] ?? 0 ?>/5)
                                <?php if (!empty($notation['livraison']['commentaire'])): ?>
                                    <br><span class="commentaire">"<?= htmlspecialchars($notation['livraison']['commentaire']) ?>"</span>
                                <?php endif; ?>
                            </div>

                            <div class="ligne-note">
                                <strong>Produit :</strong>
                                <span class="etoiles"><?= etoiles($notation['produit']['note'] ?? 0) ?></span>
                                (<?= $notation['produit']['note'] ?? 0 ?>/5)
                                <?php if (!empty($notation['produit']['commentaire'])): ?>
                                    <br><span class="commentaire">"<?= htmlspecialchars($notation['produit']['commentaire']) ?>"</span>
                                <?php endif; ?>
                            </div>

                            <div class="ligne-note">
                                <strong>Satisfaction générale :</strong>
                                <span class="etoiles"><?= etoiles($notation['satisfaction']['note'] ?? 0) ?></span>
                                (<?= $notation['satisfaction']['note'] ?? 0 ?>/5)
                                <?php if (!empty($notation['satisfaction']['commentaire'])): ?>
                                    <br><span class="commentaire">"<?= htmlspecialchars($notation['satisfaction']['commentaire']) ?>"</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="pas-de-note">Aucune évaluation pour cette commande.</p>
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
document.querySelectorAll('.bouton-details').forEach(btn => {
    btn.addEventListener('click', function() {
        const cible = document.getElementById(this.dataset.cible);
        if (cible) cible.classList.toggle('ouverte');
    });
});
</script>

<button id="btn-darkmode" class="btn-darkmode">☾</button>
<script src="darkmode.js"></script>
</body>
</html>
