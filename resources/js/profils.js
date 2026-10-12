// Profils : aperçu de l'avatar choisi, saisie du PIN (envoi automatique au 4e chiffre), confirmations.
export function initProfils() {
    // Galerie « Choisir un avatar » : l'aperçu suit la sélection
    const galerie = document.querySelector('[data-galerie-avatars]');
    if (galerie) {
        const img = document.querySelector('[data-apercu-avatar]');
        const nom = document.querySelector('[data-apercu-nom]');
        galerie.addEventListener('change', (e) => {
            const r = e.target.closest('input[type="radio"]');
            if (!r) return;
            if (img) img.src = r.dataset.src;
            if (nom) nom.textContent = r.dataset.nom;
        });
    }

    // PIN : chiffres seulement, envoi dès 4 chiffres
    document.querySelectorAll('[data-pin]').forEach((input) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '').slice(0, 4);
            if (input.value.length === 4 && input.dataset.pinAuto !== undefined) input.form?.requestSubmit();
        });
    });

    // Confirmation avant suppression d'un profil
    document.querySelectorAll('[data-confirmer]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!window.confirm(form.dataset.confirmer)) e.preventDefault();
        });
    });
}
