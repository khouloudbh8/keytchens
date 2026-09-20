// translate.js - MODIFIÉ

(function () {
    const LS = "selectedLanguage";
    const FLAGS = { fr: "/images/icons/flag-fr.svg", en: "/images/flag-en.webp" };

    const isEN = p => p === "/en" || p.startsWith("/en/");
    const stripEN = p => (p === "/en" ? "/" : p.startsWith("/en/") ? p.slice(3) : p);

    function normalizeBase(p) {
        const low = p.toLowerCase();
        if (low === "/index.html") return "/";
        if (low.endsWith("/index.html")) return p.slice(0, -"/index.html".length) + "/";
        return p;
    }

    function targetPath(lang) {
    // Si la page a défini une correspondance explicite (ex: pages articles,
    // où le slug FR/EN diffère), on l'utilise en priorité.
    if (window.KC_LANG_SWITCH_URL && window.KC_LANG_SWITCH_URL[lang]) {
        return window.KC_LANG_SWITCH_URL[lang] + location.search + location.hash;
    }

    const base = normalizeBase(stripEN(location.pathname));
    const qh = location.search + location.hash;
    return (lang === "fr")
        ? base + qh
        : (base === "/" ? "/en/" : "/en" + base) + qh;
}

    function paint(lang) {
        const img = document.querySelector("#states-button img");
        if (img && FLAGS[lang]) { img.src = FLAGS[lang]; img.alt = lang.toUpperCase(); }
        const fr = document.getElementById("french-selector");
        const en = document.getElementById("english-selector");
        fr && fr.classList.toggle("bg-gray-800", lang === "fr");
        en && en.classList.toggle("bg-gray-800", lang === "en");
        try { document.documentElement.setAttribute("lang", lang); } catch {}
    }

    function switchTo(lang) {
        if ((lang === "en" && isEN(location.pathname)) ||
            (lang === "fr" && !isEN(location.pathname))) {
            return;
        }
        try { localStorage.setItem(LS, lang); } catch {}
        location.replace(targetPath(lang));
    }

    // FONCTION PRINCIPALE - peut être appelée plusieurs fois
    function initTranslate() {
        const urlLang = isEN(location.pathname) ? "en" : "fr";

        paint(urlLang);

        try { localStorage.setItem(LS, urlLang); } catch {}

        // Ré-attacher les events (clone pour éviter les doublons)
        const frBtn = document.getElementById("french-selector");
        const enBtn = document.getElementById("english-selector");
        
        if (frBtn) {
            const newFr = frBtn.cloneNode(true);
            frBtn.parentNode.replaceChild(newFr, frBtn);
            newFr.addEventListener("click", () => switchTo("fr"));
        }
        
        if (enBtn) {
            const newEn = enBtn.cloneNode(true);
            enBtn.parentNode.replaceChild(newEn, enBtn);
            newEn.addEventListener("click", () => switchTo("en"));
        }
        
        console.log('✅ Translate initialisé');
    }

    // Premier appel
    document.addEventListener("DOMContentLoaded", initTranslate);

    // Exposer globalement
    window.initTranslate = initTranslate;
})();