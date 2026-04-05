<?php
$json = file_get_contents("data/infoclient.json");
$utilisateurs = json_decode($json, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrateur</title>
    <link rel="stylesheet" href="structg.css">
    <link rel="stylesheet" href="couleurs.css">
    <link rel="stylesheet" href="administrateur.css">
</head>
<body>
    <header>
        <div class="barres">
            <span></span>
            <span></span>
            <span></span>
        </div> 

        <h1><a href="accueil.php" class="logo">La Cour des Délices</a></h1>
        <div class="top-icons">
            <a href="profil.php"> <img src="images/Iconprofil.png" alt="Profil" class="icon"> </a>
            <a href="logout.php"><p class="deconnexion">déconnexion</p></a>
        </div>
    </header>

    <div class="search-bar">
        <input type="search" placeholder="Chercher un utilisateur">
        <button><img src="images/Iconloupe.png" alt="loupe"></button>
    </div>

    <nav class="menu-horizontal">
    <ul>
        <li>
            <a href="administrateur.php" class="active">Utilisateurs</a>
        </li>

        <li>
            <a href="administrateur2.php">Commandes</a>
        </li>
    </ul>
</nav>  

    <main>
        <button class="filtre">
            Filtrer  <img src="images/filter.png">
        </button>
        <section>
            <table>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Statut</th>
                    <th>Commandes</th>
                    <th>Remise</th>
                    <th>Actions</th>
                </tr>

                <?php foreach ($utilisateurs as $user): ?>
                <tr class="ligne">  <!--voir-->
                    <td><a href="profil_admin.php?id=<?= $user['id'] ?>"><?= $user['nom'] ?></a></td>
                    <td><?= $user['prenom'] ?></td>
                    <td><?= $user['mail'] ?></td>
                    <td><?= $user['role'] ?></td>
                    <td><?= $user['commandes'] ?></td>
                    <td><?= $user['remise'] ?>%</td>

                    <td>

                        <!-- Bloquer / débloquer -->
                        <?php if ($user['bloque']): ?>
                            <button class="filtre">Débloquer</button>
                        <?php else: ?>
                            <button class="filtre">Bloquer</button>
                        <?php endif; 
                        $id = $user['id'];
                        ?>
                        
                        <a href="modifier_admin.php?id=<?= $id ?>"><button class="filtre">Modifier</button></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </section>
    </main>


    <footer>
        <p>suivez nous sur nos réseaux!
            </br>
            <img src="images/Iconinstagram.jpg" alt="instagram" class="icon">
            <img src="images/Icontiktok.jpg" alt="tiktok" class="icon">
            <img src="images/Iconinstagram.jpg" alt="instagram" class="icon">
        </p>
        <div class="infos-footer">
            <div class="info">
                <img src="images/Iconlocalisation.png" alt="maps" class="icon">
                <span>5 av de la république, 75300 Paris</span>
            </div>
            <div class="info">
                <img src="images/Iconhorloge.png" alt="horloge" class="icon">
                <span>Tous les jours 9h - 22h</span>
            </div>
        </div>
        <h5>© 2026 Pâtisserie</h5>    
    </footer>
    
</body>
</html>
