<?php
session_start();

// Toujours répondre en JSON
header('Content-Type: application/json');

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    echo json_encode(['succes' => false, 'erreur' => 'Non connecté']);
    exit();
}

// Vérifier que c'est bien une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée']);
    exit();
}

// Charger le fichier JSON
$fichier = "data/infoclient.json";
if (!file_exists($fichier)) {
    echo json_encode(['succes' => false, 'erreur' => 'Fichier introuvable']);
    exit();
}

$users = json_decode(file_get_contents($fichier), true);
if (!is_array($users)) {
    echo json_encode(['succes' => false, 'erreur' => 'Données corrompues']);
    exit();
}

// Trouver et mettre à jour l'utilisateur connecté
$id = $_SESSION['id'];
$trouve = false;

foreach ($users as &$user) {
    if ($user['id'] == $id) {
        // Mettre à jour uniquement les champs envoyés
        $user['civilite']       = $_POST['civilite']  ?? $user['civilite'];
        $user['prenom']         = htmlspecialchars(trim($_POST['prenom']  ?? $user['prenom']));
        $user['nom']            = htmlspecialchars(trim($_POST['nom']     ?? $user['nom']));
        $user['date_naissance'] = $_POST['anniv']     ?? $user['date_naissance'];
        $user['telephone']      = htmlspecialchars(trim($_POST['tel']     ?? $user['telephone']));
        $user['adresse']        = htmlspecialchars(trim($_POST['adresse'] ?? $user['adresse']));
        $user['mail']           = htmlspecialchars(trim($_POST['mail']    ?? $user['mail']));

        $trouve = true;
        break;
    }
}
unset($user);

if (!$trouve) {
    echo json_encode(['succes' => false, 'erreur' => 'Utilisateur introuvable']);
    exit();
}

// Sauvegarder dans le fichier JSON
$ok = file_put_contents($fichier, json_encode($users, JSON_PRETTY_PRINT));

if ($ok === false) {
    echo json_encode(['succes' => false, 'erreur' => 'Impossible de sauvegarder']);
    exit();
}

// Tout s'est bien passé
echo json_encode(['succes' => true]);
