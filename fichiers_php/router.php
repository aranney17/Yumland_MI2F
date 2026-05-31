<?php
/* ROUTEUR DE SECURITE pour le serveur PHP integre.*/

// Chemin demande 
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = urldecode($uri);

// 1. Bloquer tout acces au dossier data/
if (preg_match('#^/data/#i', $uri)) {
    http_response_code(403);
    echo "Acces interdit.";
    return true; // requete traitee, on n'execute rien d'autre
}

// 2. Bloquer tout fichier .json, .log ou getapikey.php ou qu'il se trouve
if (preg_match('#\.(json|log)$#i', $uri)) {
    http_response_code(403);
    echo "Acces interdit.";
    return true;
}
if (preg_match('#getapikey\.php$#i', $uri)) {
    http_response_code(403);
    echo "Acces interdit.";
    return true;
}

// 3. Pour tout le reste : laisser le serveur servir normalement (return false = "sers le fichier demande comme d'habitude")
$fichier = __DIR__ . $uri;
if ($uri !== '/' && file_exists($fichier) && !is_dir($fichier)) {
    return false;
}

// Si ce n'est pas un fichier existant, laisser PHP gerer
return false;
