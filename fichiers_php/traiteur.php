<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: connexion.php");
    exit();
}

$fichierClients   = '../data/infoclient.json';
$fichierProduits  = '../data/produits.json';
$fichierCommandes = '../data/commande.json';

$clients  = json_decode(file_get_contents($fichierClients), true) ?? [];
$produits = json_decode(file_get_contents($fichierProduits), true) ?? [];

/* Client connecte, check bloque et role */
$client = null;
foreach ($clients as $c) {
    if ($c['id'] == $_SESSION['id']) { $client = $c; break; }
}
if (!$client) { die("Utilisateur introuvable."); }
if ($client['bloque'] ?? false) {
    session_destroy();
    die("Votre compte a été bloqué. <a href='connexion.php'>Retour</a>");
}
if ($client['role'] !== 'client') {
    header("Location: accueil.php");
    exit();
}

/* parametres de la commande traiteur */
$PRIX_ETAGE  = 30;          // prix par etage de la piece montee
$ETAGES_MAX  = 5;
$NB_ACCOMP   = 6;           // nombre maximum d'accompagnements

$GLACAGES   = ['Fraise', 'Chocolat', 'Spéculoos', 'Mangue', 'Vanille', 'Pistache'];
$GENOISES   = ['Vanille', 'Chocolat', 'Amande', 'Matcha'];
$GARNITURES = ['Pépites de chocolat', 'Morceaux de fruits frais', 'Morceaux de mangue', 'Praliné', 'Caramel beurre salé'];

/* Accompagnements (boissons exclus) */
$accompagnementsDispo = [];
$prixAccomp = [];   
foreach ($produits as $p) {
    if (($p['categorie'] ?? '') === 'Boissons') continue;
    $accompagnementsDispo[] = $p;
    $prixAccomp[$p['nom']] = (float) $p['prix'];
}

/* Images d'exemple pour le carrousel  */
$exemples = [
    'exempletraiteur1.jpg',
    'exempletraiteur2.jpg',
    'exempletraiteur3.jpg',
    'exempletraiteur4.jpg',
    'exempletraiteur5.jpg',
    'exempletraiteur6.jpg',
    'exempletraiteur7.jpg',
    'exempletraiteur8.jpg',
    'exempletraiteur9.jpg',
    'exempletraiteur10.jpg'
];

/* Recalcul du total  */
function calculerTotal($etages, $accNoms, $accQtes, $prixAccomp, $PRIX_ETAGE) {
    $total = $etages * $PRIX_ETAGE;
    foreach ($accNoms as $i => $nom) {
        if ($nom === '' || !isset($prixAccomp[$nom])) continue;
        $q = max(0, (int) ($accQtes[$i] ?? 0));
        $total += $prixAccomp[$nom] * $q;
    }
    return round($total, 2);
}

$erreur = null;
$confirmation = null;

/* retour paiement */
if (isset($_GET['status']) && isset($_SESSION['commande_traiteur'])) {

    $transaction  = $_GET['transaction'] ?? '';
    $montant      = $_GET['montant']     ?? '';
    $vendeur      = $_GET['vendeur']     ?? '';
    $status       = $_GET['status']      ?? '';
    $control_recu = $_GET['control']     ?? '';

    require('getapikey.php');
    $api_key = getAPIKey($vendeur);
    $control_calcule = md5(
        $api_key . "#" . $transaction . "#" . $montant . "#" .
        $vendeur . "#" . $status . "#"
    );

    $controleOK      = ($control_recu === $control_calcule);
    $paiementAccepte = ($controleOK && $status === "accepted");

    if ($paiementAccepte) {
        $cmdTraiteur = $_SESSION['commande_traiteur'];

        $reference = strtoupper(substr(md5(uniqid()), 0, 10));

        // Lignes "produits" : la piece montee + les accompagnements
        $lignesProduits = [];
        $lignesProduits[] = [
            "produit"  => "Pièce montée " . $cmdTraiteur['etages'] . " étage(s)",
            "prix"     => (string) ($cmdTraiteur['etages'] * $GLOBALS['PRIX_ETAGE']),
            "saveur"   => $cmdTraiteur['glacage'],
            "quantite" => "1"
        ];
        foreach ($cmdTraiteur['accompagnements'] as $a) {
            $lignesProduits[] = [
                "produit"  => $a['produit'],
                "prix"     => (string) $a['prix'],
                "saveur"   => "",
                "quantite" => (string) $a['quantite']
            ];
        }

        $commande = [
            "id"                 => time(),
            "nom"                => $client['nom']    ?? '',
            "prenom"             => $client['prenom'] ?? '',
            "date"               => date('Y-m-d'),
            "datelivraison"      => $cmdTraiteur['date_evt'],   // date de l'EVENEMENT
            "type_commande"      => "traiteur",
            "telephone"          => $client['telephone'] ?? '',
            "reference"          => $reference,
            "montant"            => $cmdTraiteur['montant'],
            "montant_paye"       => $cmdTraiteur['montant'],
            "paiement"           => "payee",
            "adresse"            => $cmdTraiteur['lieu'],        // lieu de l'evenement
            "statut"             => "a preparer",
            "notif_vue"          => false,
            "modifie_par_client" => false,
            "evenement"          => [
                "date" => $cmdTraiteur['date_evt'],
                "lieu" => $cmdTraiteur['lieu']
            ],
            "piece_montee"       => [
                "etages"    => $cmdTraiteur['etages'],
                "glacage"   => $cmdTraiteur['glacage'],
                "genoise"   => $cmdTraiteur['genoise'],
                "garniture" => $cmdTraiteur['garniture']
            ],
            "produits"           => $lignesProduits
        ];

        $commandes = json_decode(file_get_contents($fichierCommandes), true) ?? [];
        $commandes[] = $commande;
        file_put_contents($fichierCommandes, json_encode($commandes, JSON_PRETTY_PRINT));

        $confirmation = $commande;
        unset($_SESSION['commande_traiteur']);
    } else {
        $erreur = "Le paiement n'a pas abouti. Aucune commande n'a été enregistrée.";
        unset($_SESSION['commande_traiteur']);
    }
}

/* soumission du formulaire */
$lancerPaiement = false;
if (isset($_POST['payer'])) {

    $date_evt  = trim($_POST['date_evt'] ?? '');
    $lieu      = trim($_POST['lieu'] ?? '');
    $etages    = (int) ($_POST['etages'] ?? 0);
    $glacage   = trim($_POST['glacage'] ?? '');
    $genoise   = trim($_POST['genoise'] ?? '');
    $garniture = trim($_POST['garniture'] ?? '');

    $accNoms = $_POST['acc_nom'] ?? [];
    $accQtes = $_POST['acc_qte'] ?? [];

    // Validation serveur
    if ($date_evt === '' || $date_evt < date('Y-m-d')) {
        $erreur = "La date de l'événement doit être dans le futur.";
    } elseif ($lieu === '') {
        $erreur = "Veuillez indiquer le lieu de l'événement.";
    } elseif ($etages < 1 || $etages > $ETAGES_MAX) {
        $erreur = "Le nombre d'étages doit être entre 1 et $ETAGES_MAX.";
    } elseif (!in_array($glacage, $GLACAGES, true)
           || !in_array($genoise, $GENOISES, true)
           || !in_array($garniture, $GARNITURES, true)) {
        $erreur = "Choix de personnalisation invalide.";
    } else {
        // Construire la liste des accompagnements valides (max 6)
        $accompagnements = [];
        $compteur = 0;
        foreach ($accNoms as $i => $nom) {
            if ($compteur >= $NB_ACCOMP) break;
            $nom = trim($nom);
            if ($nom === '' || !isset($prixAccomp[$nom])) continue;
            $q = max(1, (int) ($accQtes[$i] ?? 1));
            // retrouver le titre lisible
            $titre = $nom;
            foreach ($accompagnementsDispo as $a) {
                if ($a['nom'] === $nom) { $titre = $a['titre']; break; }
            }
            $accompagnements[] = [
                "produit"  => $titre,
                "nom"      => $nom,
                "prix"     => $prixAccomp[$nom],
                "quantite" => $q
            ];
            $compteur++;
        }

        $total = calculerTotal($etages, $accNoms, $accQtes, $prixAccomp, $PRIX_ETAGE);

        // Stocker la commande en session jusqu'au retour de paiement
        $_SESSION['commande_traiteur'] = [
            "date_evt"        => $date_evt,
            "lieu"            => $lieu,
            "etages"          => $etages,
            "glacage"         => $glacage,
            "genoise"         => $genoise,
            "garniture"       => $garniture,
            "accompagnements" => $accompagnements,
            "montant"         => $total
        ];

        // Preparer le paiement cybank
        require('getapikey.php');
        $transaction = uniqid();
        $montant     = number_format($total, 2, '.', '');
        $vendeur     = "MI-2_F";

        $protocole = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $dossier   = rtrim(dirname($_SERVER['PHP_SELF']), '/');
        $retour    = $protocole . "://" . $_SERVER['HTTP_HOST'] . $dossier . "/traiteur.php";

        $api_key = getAPIKey($vendeur);
        $control = md5(
            $api_key . "#" . $transaction . "#" . $montant . "#" .
            $vendeur . "#" . $retour . "#"
        );

        $lancerPaiement = true;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande traiteur sur mesure</title>
    <link rel="stylesheet" href="../fichiers_css/couleurs.css">
    <link rel="stylesheet" href="../fichiers_css/structg.css">
    <link rel="stylesheet" href="../fichiers_css/darkmode.css">

</head>
<body>

<?php include 'sidebar.php'; ?>

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
<h1><a href="accueil.php" class="logo">La Cour des Délices</a></h1>

<div class="top-icons">
    <div class="profil-menu">
        <img src="../images/Iconprofil.png" alt="Profil" class="icon">
        <div class="profil-bulle">
            <a href="profil.php">Mon profil</a>
            <a href="profil2.php">Mes commandes</a>
            <a href="logout.php">Se déconnecter</a>
        </div>
    </div>
    <a href="panier.php"><img src="../images/Iconpanier.png" alt="Panier" class="icon" id="panier"></a>
</div>

<nav class="menu-horizontal">
    <ul>
        <li><a href="menu.php">Menus</a></li>
        <li><a href="presentation.php">Tous nos produits</a></li>
        <li><a href="traiteur.php" class="active">Commande traiteur</a></li>
    </ul>
</nav>

<div class="traiteur">

<?php if ($confirmation): ?>
    <!--  CONFIRMATION APRES PAIEMENT  -->
    <h1 style="text-align:center;">Merci pour votre commande !</h1>
    <div class="msg-ok">
        <p>Votre commande traiteur a bien été enregistrée et payée.</p>
        <ul class="detail-recap">
            <li><strong>Référence :</strong> <?= htmlspecialchars($confirmation['reference']) ?></li>
            <li><strong>Date de l'événement :</strong> <?= htmlspecialchars($confirmation['evenement']['date']) ?></li>
            <li><strong>Lieu :</strong> <?= htmlspecialchars($confirmation['evenement']['lieu']) ?></li>
            <li><strong>Pièce montée :</strong>
                <?= htmlspecialchars($confirmation['piece_montee']['etages']) ?> étage(s),
                glaçage <?= htmlspecialchars($confirmation['piece_montee']['glacage']) ?>,
                génoise <?= htmlspecialchars($confirmation['piece_montee']['genoise']) ?>,
                garniture <?= htmlspecialchars($confirmation['piece_montee']['garniture']) ?>
            </li>
            <li><strong>Montant payé :</strong> <?= number_format($confirmation['montant'], 2, ',', ' ') ?> €</li>
        </ul>
        <p>Notre cuisinier préparera votre commande à l'approche de la date.</p>
    </div>
    <p style="text-align:center;"><a href="accueil.php" class="btn-payer" style="max-width:300px;margin:0 auto;text-align:center;text-decoration:none;">Revenir à l'accueil</a></p>

<?php elseif ($lancerPaiement): ?>
    <!-- REDIRECTION VERS LE PAIEMENT  -->
    <h2>Redirection vers le paiement sécurisé…</h2>
    <form id="cybankForm" action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
        <input type="hidden" name="transaction" value="<?= $transaction ?>">
        <input type="hidden" name="montant"     value="<?= $montant ?>">
        <input type="hidden" name="vendeur"     value="<?= $vendeur ?>">
        <input type="hidden" name="retour"      value="<?= $retour ?>">
        <input type="hidden" name="control"     value="<?= $control ?>">
    </form>
    <script>document.getElementById('cybankForm').submit();</script>

<?php else: ?>
    <!-- FORMULAIRE TRAITEUR  -->
    <h1 style="text-align:center;">Commande traiteur sur mesure</h1>
    <p style="text-align:center;">Composez le buffet de votre événement : pièce montée personnalisée + accompagnements.</p>

    <?php if ($erreur): ?>
        <div class="msg-erreur"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <form method="POST" id="form-traiteur">

        <!-- evenement -->
        <h2>Votre événement</h2>
        <div class="bloc">
            <div class="champ-ligne">
                <label for="date_evt">Date de l'événement</label>
                <input type="date" id="date_evt" name="date_evt" min="<?= date('Y-m-d') ?>"
                       value="<?= htmlspecialchars($_POST['date_evt'] ?? '') ?>" required>
            </div>
            <div class="champ-ligne">
                <label for="lieu">Lieu de l'événement</label>
                <input type="text" id="lieu" name="lieu" placeholder="Adresse / salle"
                       value="<?= htmlspecialchars($_POST['lieu'] ?? '') ?>" required>
            </div>
        </div>

        <!-- piece monté -->
        <h2>Choix de la pièce montée</h2>
        <div class="bloc piece-montee">
            <div class="piece-form">
                <div class="champ-ligne">
                    <label for="etages">Nombre d'étages (<?= $PRIX_ETAGE ?>€/étage)</label>
                    <select id="etages" name="etages" onchange="recalc()">
                        <?php for ($i = 1; $i <= $ETAGES_MAX; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?> étage<?= $i > 1 ? 's' : '' ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="champ-ligne">
                    <label for="glacage">Parfum du glaçage / crème</label>
                    <select id="glacage" name="glacage">
                        <?php foreach ($GLACAGES as $g): ?>
                            <option value="<?= htmlspecialchars($g) ?>"><?= htmlspecialchars($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="champ-ligne">
                    <label for="genoise">Goût de la génoise</label>
                    <select id="genoise" name="genoise">
                        <?php foreach ($GENOISES as $g): ?>
                            <option value="<?= htmlspecialchars($g) ?>"><?= htmlspecialchars($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="champ-ligne">
                    <label for="garniture">Insert / garniture</label>
                    <select id="garniture" name="garniture">
                        <?php foreach ($GARNITURES as $g): ?>
                            <option value="<?= htmlspecialchars($g) ?>"><?= htmlspecialchars($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Carrousel d'exemples  -->
            <div class="piece-carrousel">
                <p style="font-weight:bold;">Quelques exemples :</p>
                <div class="carrousel">
                    <button type="button" class="fleche" onclick="defiler(-1)">&#10094;</button>
                    <div class="carrousel-images" id="carrousel-images">
                        <?php foreach ($exemples as $img): ?>
                            <img src="../images/<?= htmlspecialchars($img) ?>" alt="Exemple de gâteau">
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="fleche" onclick="defiler(1)">&#10095;</button>
                </div>
            </div>
        </div>

        <!-- accompagnements -->
        <h2>Accompagnements (6 maximum)</h2>
        <div class="bloc">
            <?php for ($i = 0; $i < $NB_ACCOMP; $i++): ?>
                <div class="acc-ligne">
                    <span style="min-width:130px;">Accompagnement <?= $i + 1 ?> :</span>
                    <select name="acc_nom[<?= $i ?>]" onchange="recalc()">
                        <option value="">— Aucun —</option>
                        <?php foreach ($accompagnementsDispo as $a): ?>
                            <option value="<?= htmlspecialchars($a['nom']) ?>">
                                <?= htmlspecialchars($a['titre']) ?> (<?= number_format($a['prix'], 2, ',', ' ') ?> €)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label>Qté :
                        <input type="number" name="acc_qte[<?= $i ?>]" value="1" min="1" max="200" oninput="recalc()">
                    </label>
                    <span class="acc-prix" id="acc-prix-<?= $i ?>">0,00 €</span>
                </div>
            <?php endfor; ?>
        </div>

        <div class="total-traiteur">
            Total : <span id="total-affiche">0,00</span> €
        </div>

        <button type="submit" name="payer" class="btn-payer">Valider et payer</button>
    </form>
<?php endif; ?>

</div>

<footer>
    <p>suivez nous sur nos réseaux!<br>
        <img src="../images/Iconinstagram.jpg" class="icon">
        <img src="../images/Icontiktok.jpg" class="icon">
        <img src="../images/Icontwitter.png" class="icon">
    </p>
    <div class="infos-footer">
        <div class="info"><img src="../images/Iconlocalisation.png" class="icon"><span>5 avenue de la république, 75015 Paris</span></div>
        <div class="info"><img src="../images/Iconhorloge.png" class="icon"><span>Tous les jours 9h - 20h</span></div>
    </div>
    <h5>© 2026 Pâtisserie</h5>
</footer>

<script>
const PRIX_ETAGE  = <?= $PRIX_ETAGE ?>;
const PRIX_ACCOMP = <?= json_encode($prixAccomp) ?>;
const NB_ACCOMP   = <?= $NB_ACCOMP ?>;

function recalc() {
    let total = 0;

    const etages = parseInt(document.getElementById('etages').value) || 0;
    total += etages * PRIX_ETAGE;

    for (let i = 0; i < NB_ACCOMP; i++) {
        const sel = document.querySelector('[name="acc_nom[' + i + ']"]');
        const qteInput = document.querySelector('[name="acc_qte[' + i + ']"]');
        const span = document.getElementById('acc-prix-' + i);
        if (!sel || !qteInput || !span) continue;

        const nom = sel.value;
        let qte = parseInt(qteInput.value) || 0;
        if (qte < 1) qte = 1;

        if (nom && PRIX_ACCOMP[nom] !== undefined) {
            const sousTotal = PRIX_ACCOMP[nom] * qte;
            span.textContent = sousTotal.toFixed(2).replace('.', ',') + ' €';
            total += sousTotal;
        } else {
            span.textContent = '0,00 €';
        }
    }

    document.getElementById('total-affiche').textContent = total.toFixed(2).replace('.', ',');
}

/* Carrousel d'exemples */
function defiler(sens) {
    const c = document.getElementById('carrousel-images');
    if (c) c.scrollBy({ left: sens * 312, behavior: 'smooth' });
}

document.addEventListener('DOMContentLoaded', recalc);
</script>

<button id="btn-darkmode" class="btn-darkmode">☾</button>
<script src="../fichiers_js/darkmode.js"></script>
</body>
</html>
