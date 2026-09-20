// src/app/index.js
async function loadPartialFSD(placeholderId, partialPath) {
    const placeholder = document.getElementById(placeholderId);
    if (!placeholder) {
        console.warn(`Placeholder #${placeholderId} non trouve`);
        return;
    }
    try {
        const response = await fetch(partialPath);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const html = await response.text();
        placeholder.innerHTML = html;
        console.log(`${partialPath} charge`);
    } catch (error) {
        console.error(`Erreur:`, error);
    }
}
function getLanguage() {
    if (window.location.pathname.includes('/en/')) return 'en';
    if (localStorage.getItem('selectedLanguage') === 'en') return 'en';
    return 'fr';
}
window.getLanguage = getLanguage;
document.addEventListener('DOMContentLoaded', async () => {
    const lang = getLanguage();
    console.log('Langue detectee:', lang);

    // 1. Charger la navbar
    const navbarPath = lang === 'en'
        ? '/src/widgets/navbar/navbar-en.html'
        : '/src/widgets/navbar/navbar.html';
    await loadPartialFSD('navbar-placeholder', navbarPath);

    // 2. Ré-initialiser translate.js APRÈS injection
    if (typeof window.initTranslate === 'function') {
        window.initTranslate();
    }
    // 3. Ré-initialiser navbar.js APRÈS injection
    if (typeof window.initNavbar === 'function') {
    window.initNavbar();
    }
    // 4. Charger la section Partenaires & Avis
    const partnerreviewPath = lang === 'en'
        ? '/src/widgets/partners&review/section_partners_en.html'
        : '/src/widgets/partners&review/section_partners.html';
    await loadPartialFSD('partnerreview-placeholder', partnerreviewPath);

    // 5. Charger le footer
    const footerPath = lang === 'en'
        ? '/src/widgets/footer/footer-en.html'
        : '/src/widgets/footer/footer.html';
    await loadPartialFSD('footer-placeholder', footerPath);
});