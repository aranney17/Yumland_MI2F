// Lire un cookie
function getCookie(nom) {
    const cookies = document.cookie.split('; ');
    for (let c of cookies) {
        const [cle, val] = c.split('=');
        if (cle === nom) return val;
    }
    return null;
}

// Ecrire un cookie
function setCookie(nom, val, jours) {
    const date = new Date();
    date.setTime(date.getTime() + jours * 24 * 60 * 60 * 1000);
    document.cookie = nom + "=" + val + "; expires=" + date.toUTCString() + "; path=/";
}

// Au chargement de la page
document.addEventListener('DOMContentLoaded', function() {

    const btn = document.getElementById('btn-darkmode');
    if (!btn) return; // page sans bouton, on ne fait rien

    // Appliquer la preference sauvegardee
    const darkmode = getCookie('darkmode');
    if (darkmode === 'true') {
        document.body.classList.add('dark');
        btn.textContent = '☀';
    } else {
        btn.textContent = '☾';
    }

    // annimation au clic
    btn.addEventListener('click', function() {
        document.body.classList.toggle('dark');
        const estActif = document.body.classList.contains('dark');
        setCookie('darkmode', estActif ? 'true' : 'false', 365);
        btn.textContent = estActif ? '☀' : '☾';
    });
});

const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebarOverlay');

document.querySelector('.barres').addEventListener('click', () => {
    sidebar.classList.toggle('open');
    overlay.classList.toggle('open');
});
overlay.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.classList.remove('open');
});
sidebar.querySelectorAll('a').forEach(a =>
    a.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
    })
);
