<?php
// vitrine/blog/article.php
// Page article générée en PHP pur (sans Twig).
// Elle appelle l'API JSON existante (Symfony/blog-api) et construit
// le HTML côté serveur, avant l'envoi au navigateur.

declare(strict_types=1);

// --- 1. Récupérer le slug demandé (via /blog/{slug} réécrit par .htaccess,
//         ou directement via ?slug=... en attendant la réécriture) ---
$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';

if ($slug === '') {
    http_response_code(404);
    echo "Article introuvable.";
    exit;
}

// --- 2. Appeler l'API Symfony pour récupérer les données de l'article ---
const API_BASE = 'http://blog-api.test/api';

function fetchJson(string $url): ?array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $status !== 200) {
        return null;
    }

    $data = json_decode($response, true);
    return is_array($data) ? $data : null;
}

$article = fetchJson(API_BASE . '/articles/' . rawurlencode($slug));

if ($article === null) {
    http_response_code(404);
    // Idéalement, afficher ici une vraie page 404 stylée du site.
    echo "<h1>Article introuvable</h1><p><a href='/blog.html'>Retour au blog</a></p>";
    exit;
}

// --- 3. Récupérer les articles liés (même catégorie) pour "À lire aussi" ---
$allArticles = fetchJson(API_BASE . '/articles') ?? [];
$related = array_values(array_filter(
    $allArticles,
    fn(array $a) => ($a['slug'] ?? null) !== $slug
        && ($a['category'] ?? null) === ($article['category'] ?? null)
));
$related = array_slice($related, 0, 3);

// --- 4. Petits utilitaires ---
function e(?string $value): string
{
    // Échappement systématique : équivalent PHP de l'auto-échappement de Twig.
    // Sans ça, un titre ou contenu malveillant pourrait injecter du HTML/JS (XSS).
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function formatDateFr(?string $isoDate): string
{
    if (!$isoDate) {
        return '';
    }
    $timestamp = strtotime($isoDate);
    if ($timestamp === false) {
        return e($isoDate);
    }
    $months = [
        1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
        5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
        9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
    ];
    return sprintf(
        '%d %s %d',
        (int) date('j', $timestamp),
        $months[(int) date('n', $timestamp)],
        (int) date('Y', $timestamp)
    );
}

$title           = $article['title'] ?? '';
$metaDescription = $article['metaDescription'] ?? ($article['excerpt'] ?? '');
$image           = $article['image'] ?? '';
$category        = $article['category'] ?? '';
$date            = $article['date'] ?? '';
$content         = $article['content'] ?? ''; // HTML de confiance venant de la base, non ré-échappé volontairement
$canonicalUrl    = 'https://www.keytchens.com/blog/' . rawurlencode($slug);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <base href="/">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Meta générées dynamiquement en PHP, remplies AVANT l'envoi au navigateur -->
    <title><?= e($title) ?> - Blog Keytchens</title>
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <meta name="description" content="<?= e($metaDescription) ?>">
    <meta name="robots" content="index, follow">

    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <meta property="og:image" content="<?= e($image) ?>">
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="article:published_time" content="<?= e($date) ?>">
    <meta property="article:section" content="<?= e($category) ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($title) ?>">
    <meta name="twitter:description" content="<?= e($metaDescription) ?>">
    <meta name="twitter:image" content="<?= e($image) ?>">

    <link rel="apple-touch-icon" sizes="180x180" href="/images/keytchens-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/keytchens-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <meta name="theme-color" content="#ffffff">
    <link href="/src/shared/styles/output.css" rel="stylesheet">

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BlogPosting",
        "headline": <?= json_encode($title, JSON_UNESCAPED_UNICODE) ?>,
        "image": <?= json_encode($image, JSON_UNESCAPED_UNICODE) ?>,
        "datePublished": <?= json_encode($date, JSON_UNESCAPED_UNICODE) ?>,
        "author": { "@type": "Organization", "name": "Keytchens" },
        "publisher": { "@type": "Organization", "name": "Keytchens" }
    }
    </script>

    <script async src="https://www.googletagmanager.com/gtag/js?id=G-YEENPYW1B2"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-YEENPYW1B2');
    </script>

    <style>
        .kc-share-btn {
            width: 2.25rem; height: 2.25rem;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 9999px; color: #9ca3af; background: transparent;
            transition: all .2s ease;
        }
        .kc-share-btn:hover { transform: translateY(-2px); }
        .kc-share-fb:hover { background: #1877F2; color: #fff; }
        .kc-share-li:hover { background: #0A66C2; color: #fff; }
        .kc-share-ig:hover {
            background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%);
            color: #fff;
        }
        #kc-toast {
            position: fixed; bottom: 1.5rem; left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: #111827; color: #fff; padding: .6rem 1.2rem;
            border-radius: .5rem; font-size: .875rem; opacity: 0;
            pointer-events: none; transition: all .25s ease; z-index: 100;
        }
        #kc-toast.kc-show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="font-public bg-white dark:bg-gray-900">

<section class="relative bg-darkblue overflow-hidden">
    <div class="h-56 relative">
        <div id="navbar-placeholder" style="position: relative; z-index: 50;"></div>

        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>
        </div>
    </div>
</section>

<div class="max-w-screen-md mx-auto px-4 pt-12 pb-8 text-center">
    <p class="text-sm font-semibold text-primary uppercase tracking-wide mb-3">
        <?= e($category) ?>
    </p>
    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-4 tracking-tight">
        <?= e($title) ?>
    </h1>
    <time datetime="<?= e($date) ?>" class="text-sm text-gray-500 dark:text-gray-400">
        <?= formatDateFr($date) ?>
    </time>
</div>

<div class="max-w-2xl mx-auto px-4 mb-4">
    <img src="<?= e($image) ?>" alt="<?= e($title) ?>"
         class="w-full h-auto max-h-96 object-cover rounded-2xl shadow-md">
</div>

<main>
    <article class="max-w-screen-md mx-auto px-4 py-12">
        <div class="prose prose-lg prose-gray dark:prose-invert max-w-none">
            <?= $content /* contenu HTML de confiance venant de la base, non échappé volontairement */ ?>
        </div>

        <div class="mt-10 pt-6 border-t border-gray-100 dark:border-gray-800 flex items-center gap-2">
            <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">Partager :</span>
            <button type="button" class="kc-share-btn kc-share-li" data-share="linkedin" title="LinkedIn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M20.45 20.45h-3.55v-5.57c0-1.33-.02-3.03-1.85-3.03-1.86 0-2.15 1.45-2.15 2.94v5.66H9.35V9h3.41v1.56h.05c.47-.9 1.63-1.85 3.36-1.85 3.6 0 4.26 2.37 4.26 5.45v6.29ZM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12ZM7.12 20.45H3.56V9h3.56v11.45Z"/>
                </svg>
            </button>
            <button type="button" class="kc-share-btn kc-share-fb" data-share="facebook" title="Facebook">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.91h2.54V9.85c0-2.51 1.49-3.9 3.77-3.9 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.44 2.91h-2.34V22c4.78-.76 8.44-4.92 8.44-9.94Z"/>
                </svg>
            </button>
            <button type="button" class="kc-share-btn kc-share-ig" data-share="instagram" title="Instagram">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="2.5" y="2.5" width="19" height="19" rx="5"/>
                    <circle cx="12" cy="12" r="4.2"/>
                    <circle cx="17.4" cy="6.6" r="1.1" fill="currentColor" stroke="none"/>
                </svg>
            </button>
        </div>
    </article>

    <?php if (!empty($related)): ?>
    <section class="bg-gray-50 dark:bg-gray-800/50 py-16">
        <div class="max-w-screen-xl mx-auto px-4">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">À lire aussi</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <?php foreach ($related as $a): ?>
                <a href="/blog/<?= e($a['slug'] ?? '') ?>"
                   class="block bg-white dark:bg-gray-800 rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-lg transition">
                    <img src="<?= e($a['image'] ?? '') ?>" alt="<?= e($a['title'] ?? '') ?>" class="w-full h-40 object-cover">
                    <div class="p-4">
                        <time class="text-xs text-gray-400 dark:text-gray-500"><?= formatDateFr($a['date'] ?? null) ?></time>
                        <h3 class="mt-1 font-semibold text-gray-900 dark:text-white line-clamp-2"><?= e($a['title'] ?? '') ?></h3>
                        <span class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-primary">
                            Lire l'article
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
</main>

<div id="footer-placeholder"></div>

<script src="https://unpkg.com/flowbite@1.4.1/dist/flowbite.js"></script>
<script src="/src/widgets/navbar/navbar.js"></script>
<script src="/src/shared/lang/translate.js"></script>
<script type="module" src="/src/app/index.js"></script>

<script>
    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }
        return new Promise((resolve, reject) => {
            try {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                const ok = document.execCommand('copy');
                document.body.removeChild(textarea);
                ok ? resolve() : reject();
            } catch (err) { reject(err); }
        });
    }

    function showToast(message) {
        let toast = document.getElementById('kc-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'kc-toast';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.classList.add('kc-show');
        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => toast.classList.remove('kc-show'), 2500);
    }

    document.querySelectorAll('.kc-share-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const network = btn.dataset.share;
            const url = window.location.href;

            if (network === 'linkedin') {
                window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`, 'share-li', 'width=580,height=520');
            } else if (network === 'facebook') {
                window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, 'share-fb', 'width=580,height=520');
            } else if (network === 'instagram') {
                copyToClipboard(url)
                    .then(() => showToast('Lien copié ! Collez-le dans votre story ou bio Instagram.'))
                    .catch(() => showToast('Impossible de copier le lien automatiquement.'));
            }
        });
    });
</script>
</body>
</html>