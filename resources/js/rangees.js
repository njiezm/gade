// Rangées horizontales : flèches (souris), clavier (← →), doigt (défilement natif).
// Le débordement reste dans la piste (overflow-x: auto), jamais sur la page.
export function initRangees(root = document) {
    root.querySelectorAll('[data-rangee]').forEach((rangee) => {
        if (rangee.dataset.pret) return;
        rangee.dataset.pret = '1';
        const piste = rangee.querySelector('[data-piste]');
        const prec = rangee.querySelector('[data-fleche="prec"]');
        const suiv = rangee.querySelector('[data-fleche="suiv"]');
        if (!piste) return;
        const pas = () => Math.max(piste.clientWidth * 0.85, 200);
        const maj = () => {
            const max = piste.scrollWidth - piste.clientWidth - 2;
            if (prec) prec.disabled = piste.scrollLeft <= 2;
            if (suiv) suiv.disabled = piste.scrollLeft >= max;
        };
        prec?.addEventListener('click', () => piste.scrollBy({ left: -pas(), behavior: 'smooth' }));
        suiv?.addEventListener('click', () => piste.scrollBy({ left: pas(), behavior: 'smooth' }));
        piste.addEventListener('scroll', maj, { passive: true });
        window.addEventListener('resize', maj, { passive: true });
        piste.addEventListener('keydown', (e) => {
            if (e.target !== piste) return;
            if (e.key === 'ArrowRight') { e.preventDefault(); piste.scrollBy({ left: pas(), behavior: 'smooth' }); }
            if (e.key === 'ArrowLeft') { e.preventDefault(); piste.scrollBy({ left: -pas(), behavior: 'smooth' }); }
        });
        // Quand on navigue au clavier dans les cartes, la carte reçoit le focus et reste visible
        piste.addEventListener('focusin', (e) => {
            const li = e.target.closest('li');
            if (li && e.target !== piste) li.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'smooth' });
        });
        maj();
    });
}
