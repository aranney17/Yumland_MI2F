<?php
session_start();
session_destroy();

header("Location: ../fichiers_php/accueil.php");
exit();
