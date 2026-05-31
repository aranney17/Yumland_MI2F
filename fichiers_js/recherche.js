/* ============================================================
   AUTOCOMPLETION DE LA BARRE DE RECHERCHE
   - Ecoute la frappe dans .search-bar input[type=search]
   - Interroge recherche.php?ajax=1&q=...
   - Affiche des suggestions cliquables -> produits.php?nom=...
   Fonctionne sur toutes les pages qui ont une .search-bar.
============================================================ */
(function () {
    function debounce(fn, delai) {
        let t;
        return function () {
            clearTimeout(t);
            const args = arguments, ctx = this;
            t = setTimeout(function () { fn.apply(ctx, args); }, delai);
        };
    }

    document.querySelectorAll('.search-bar').forEach(function (bar) {
        const input = bar.querySelector('input[type="search"]');
        if (!input) return;

        // La barre doit pouvoir contenir la liste en position absolue
        bar.style.position = 'relative';

        // Conteneur des suggestions
        const box = document.createElement('div');
        box.className = 'recherche-suggestions';
        box.style.cssText =
            'position:absolute;top:100%;left:0;right:0;z-index:3000;' +
            'background:var(--surface,#ffffff);' +
            'border:1px solid var(--bordure,#ccc);' +
            'border-top:none;border-radius:0 0 12px 12px;' +
            'max-height:340px;overflow-y:auto;display:none;' +
            'box-shadow:0 8px 20px rgba(0,0,0,0.15);';
        bar.appendChild(box);

        function cacher() { box.style.display = 'none'; box.innerHTML = ''; }
        function afficher() { box.style.display = 'block'; }

        input.addEventListener('input', debounce(function () {
            const q = input.value.trim();
            if (q.length < 1) { cacher(); return; }

            fetch('recherche.php?ajax=1&q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (liste) {
                    if (!liste || liste.length === 0) { cacher(); return; }
                    box.innerHTML = '';

                    liste.forEach(function (p) {
                        const a = document.createElement('a');
                        a.href = 'produits.php?nom=' + encodeURIComponent(p.nom);
                        a.style.cssText =
                            'display:flex;align-items:center;gap:10px;' +
                            'padding:8px 12px;text-decoration:none;' +
                            'color:var(--texte,#322119);' +
                            'border-bottom:1px solid var(--bordure,#eee);';

                        const img = document.createElement('img');
                        img.src = '../images/' + p.image;
                        img.alt = '';
                        img.style.cssText = 'width:40px;height:40px;object-fit:cover;border-radius:6px;flex-shrink:0;';

                        const span = document.createElement('span');
                        const prix = Number(p.prix).toFixed(2).replace('.', ',');
                        span.textContent = p.titre + ' — ' + prix + '€';

                        a.appendChild(img);
                        a.appendChild(span);

                        a.addEventListener('mouseover', function () { a.style.background = 'var(--surface-2, #f3eee6)'; });
                        a.addEventListener('mouseout',  function () { a.style.background = 'transparent'; });

                        box.appendChild(a);
                    });

                    afficher();
                })
                .catch(function () { cacher(); });
        }, 200));

        // Fermer si on clique en dehors
        document.addEventListener('click', function (e) {
            if (!bar.contains(e.target)) cacher();
        });
    });
})();
