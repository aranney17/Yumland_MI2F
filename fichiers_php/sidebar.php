
<div class="nav-laterale-overlay" id="navLateraleOverlay"></div>
<nav class="nav-laterale" id="navLaterale" aria-label="Catégories">
    <button type="button" class="nav-laterale-fermer" id="navLateraleFermer" aria-label="Fermer le menu">&times;</button>
    <a href="accueil.php#nouveautes">Nouveautés</a>
    <a href="presentation.php#viennoiseries">Viennoiseries</a>
    <a href="presentation.php#boissons">Boissons</a>
    <a href="presentation.php#gourmandises">Gourmandises</a>
    <a href="presentation.php#patisseries">Pâtisseries</a>
    <a href="presentation.php#gateaux">Gâteaux</a>
    <a href="presentation.php#tartes">Tartes</a>
    <a href="traiteur.php">Commande traiteur</a>
</nav>

<style>
.nav-laterale {
    position: fixed; top: 0; left: 0; height: 100%; width: 260px;
    background: var(--surface); color: var(--texte);
    box-shadow: 2px 0 12px rgba(0, 0, 0, 0.25);
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    z-index: 10001;
    display: flex; flex-direction: column;
    padding-top: 60px;
}
.nav-laterale.open { transform: translateX(0); }
.nav-laterale a {
    padding: 14px 26px;
    color: var(--texte);
    text-decoration: none;
    border-bottom: 1px solid var(--bordure);
    font-family: "DM Serif Display", serif;
}
.nav-laterale a:hover { background: var(--hover-ligne); color: var(--accent); }
.nav-laterale-fermer {
    position: absolute; top: 12px; right: 16px;
    background: none; border: none; color: var(--texte);
    font-size: 28px; line-height: 1; cursor: pointer;
}
.nav-laterale-overlay {
    position: fixed; inset: 0;
    background: rgba(0, 0, 0, 0.45);
    opacity: 0; visibility: hidden;
    transition: opacity 0.3s;
    z-index: 10000;
}
.nav-laterale-overlay.open { opacity: 1; visibility: visible; }
</style>

<script>
(function () {
    var nav     = document.getElementById('navLaterale');
    var overlay = document.getElementById('navLateraleOverlay');
    var fermer  = document.getElementById('navLateraleFermer');
    var burger  = document.querySelector('.barres');
    if (!nav) return;

    function ouvrir()  { nav.classList.add('open');    if (overlay) overlay.classList.add('open'); }
    function fermerNav() { nav.classList.remove('open'); if (overlay) overlay.classList.remove('open'); }

    if (burger) {
        burger.style.cursor = 'pointer';
        burger.addEventListener('click', function (e) {
            e.stopPropagation();
            if (nav.classList.contains('open')) { fermerNav(); } else { ouvrir(); }
        });
    }
    if (overlay) overlay.addEventListener('click', fermerNav);
    if (fermer)  fermer.addEventListener('click', fermerNav);
})();
</script>
