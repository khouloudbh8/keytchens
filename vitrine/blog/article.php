<?php
declare(strict_types=1);

$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';

if ($slug === '') {
    http_response_code(404);
    echo "Article introuvable.";
    exit;
}

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
    echo "<h1>Article introuvable</h1><p><a href='/blog.html'>Retour au blog</a></p>";
    exit;
}
$translationData = fetchJson(API_BASE . '/articles/' . rawurlencode($slug) . '/translation');
$enSlug = $translationData['slug'] ?? null;
$langSwitchUrl = $enSlug ? "/en/blog/{$enSlug}" : '/en/blog.html';

$allArticles = fetchJson(API_BASE . '/articles?lang=fr') ?? [];

$related = array_values(array_filter(
    $allArticles,
    fn(array $a) => ($a['slug'] ?? null) !== $slug
        && ($a['category'] ?? null) === ($article['category'] ?? null)
));
$related = array_slice($related, 0, 3);


$recent = array_values(array_filter(
    $allArticles,
    fn(array $a) => ($a['slug'] ?? null) !== $slug
));
$recent = array_slice($recent, 0, 4);

$slugs = array_column($allArticles, 'slug');
$currentIndex = array_search($slug, $slugs, true);
$prevArticle = ($currentIndex !== false && $currentIndex > 0) ? $allArticles[$currentIndex - 1] : null;
$nextArticle = ($currentIndex !== false && $currentIndex < count($allArticles) - 1) ? $allArticles[$currentIndex + 1] : null;


function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function formatDateFr(?string $isoDate): string
{
    if (!$isoDate) return '';
    $timestamp = strtotime($isoDate);
    if ($timestamp === false) return e($isoDate);
    $months = [
        1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
        5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
        9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
    ];
    return sprintf('%d %s %d', (int) date('j', $timestamp), $months[(int) date('n', $timestamp)], (int) date('Y', $timestamp));
}

function readingTime(string $content): int
{
    $text = strip_tags($content);
    $words = str_word_count($text);
    return max(1, ceil($words / 200));
}

$title           = $article['title'] ?? '';
$metaDescription = $article['metaDescription'] ?? ($article['excerpt'] ?? '');
$image           = $article['image'] ?? '';
$category        = $article['category'] ?? '';
$date            = $article['date'] ?? '';
$content         = $article['content'] ?? '';
$gallery         = $article['gallery'] ?? [];
$canonicalUrl    = 'https://www.keytchens.com/blog/' . rawurlencode($slug);
$readTime        = readingTime($content);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <base href="/">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
        /* ===== ANIMATIONS ===== */
        @keyframes kc-fade-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .kc-fade-up {
            opacity: 0;
            animation: kc-fade-up 0.7s ease-out forwards;
        }
        .kc-delay-1 { animation-delay: 0.1s; }
        .kc-delay-2 { animation-delay: 0.2s; }
        .kc-delay-3 { animation-delay: 0.3s; }

        /* ===== PROSE CUSTOM ===== */
        .kc-prose h2 {
            font-size: 1.5rem; font-weight: 700; margin-top: 2.5rem; margin-bottom: 1rem;
            color: #0f172a;
        }
        .dark .kc-prose h2 { color: #f8fafc; }
        .kc-prose h3 {
            font-size: 1.25rem; font-weight: 600; margin-top: 2rem; margin-bottom: 0.75rem;
            color: #1e293b;
        }
        .dark .kc-prose h3 { color: #e2e8f0; }
        .kc-prose p { margin-bottom: 1.25rem; line-height: 1.8; color: #334155; }
        .dark .kc-prose p { color: #cbd5e1; }
        .kc-prose ul { list-style: none; padding-left: 0; margin-bottom: 1.5rem; }
        .kc-prose ul li {
            position: relative; padding-left: 1.75rem; margin-bottom: 0.75rem;
            line-height: 1.7; color: #334155;
        }
        .dark .kc-prose ul li { color: #cbd5e1; }
        .kc-prose ul li::before {
            content: "";
            position: absolute; left: 0; top: 0.55rem;
            width: 6px; height: 6px; border-radius: 50%;
            background: #10b981;
        }
        .kc-prose blockquote {
            border-left: 4px solid #10b981;
            background: #f0fdf4; padding: 1.25rem 1.5rem;
            border-radius: 0 0.75rem 0.75rem 0; margin: 2rem 0;
            font-style: italic; color: #14532d;
        }
        .dark .kc-prose blockquote {
            background: #064e3b; color: #86efac; border-left-color: #34d399;
        }
        .kc-prose img {
            border-radius: 1rem; margin: 2rem 0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }

        /* ===== SHARE ===== */
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
        .kc-share-copy:hover { background: #111827; color: #fff; }

        /* ===== TOAST ===== */
        #kc-toast {
            position: fixed; bottom: 1.5rem; left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: #111827; color: #fff; padding: .6rem 1.4rem;
            border-radius: .5rem; font-size: .875rem; opacity: 0;
            pointer-events: none; transition: all .25s ease; z-index: 100;
            white-space: nowrap;
        }
        #kc-toast.kc-show { opacity: 1; transform: translateX(-50%) translateY(0); }

        /* ===== NAV PREV/NEXT ===== */
        .kc-nav-card {
            border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.25rem;
            transition: all 0.3s; background: #fff;
        }
        .kc-nav-card:hover { border-color: #10b981; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08); }
        .dark .kc-nav-card { background: #1e293b; border-color: #334155; }
        .dark .kc-nav-card:hover { border-color: #059669; }

        /* ===== GALLERY ===== */
        .kc-gallery { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin: 2rem 0; }
        .kc-gallery img { width: 100%; height: 200px; object-fit: cover; border-radius: 0.75rem; margin: 0; transition: transform 0.3s; cursor: pointer; }
        .kc-gallery img:hover { transform: scale(1.03); }
        @media (min-width: 768px) { .kc-gallery { grid-template-columns: repeat(3, 1fr); } }

        /* ===== CTA BOX ===== */
        .kc-cta-box {
    background: rgba(16, 185, 129, 0.10);
    border: 1px solid rgba(16, 185, 129, 0.25);
    border-radius: 1rem; padding: 1.5rem; color: #065f46; position: relative; overflow: hidden;
}
.dark .kc-cta-box {
    background: rgba(16, 185, 129, 0.08);
    border-color: rgba(52, 211, 153, 0.25);
    color: #6ee7b7;
}
.kc-cta-box::before {
    content: ""; position: absolute; top: -50%; right: -20%;
    width: 200px; height: 200px; background: rgba(16, 185, 129, 0.08);
    border-radius: 50%;
}

        /* ===== LINE CLAMP ===== */
        .line-clamp-2 {
            display: -webkit-box; -webkit-line-clamp: 2;
            -webkit-box-orient: vertical; overflow: hidden;
        }
        .line-clamp-3 {
            display: -webkit-box; -webkit-line-clamp: 3;
            -webkit-box-orient: vertical; overflow: hidden;
        }

        @media (prefers-reduced-motion: reduce) {
            .kc-fade-up { animation: none; opacity: 1; }
        }
    </style>
</head>
<body class="font-public bg-white dark:bg-gray-900">

<!-- ==================== HERO IDENTIQUE AU BLOG ==================== -->
<section class="relative bg-darkblue overflow-hidden">
    <div class="h-96 relative">
        <div id="navbar-placeholder" style="position: relative; z-index: 50;"></div>

        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl animate-pulse-slow"></div>
            <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl animate-pulse-slow-delay"></div>
        </div>

        <div class="flex justify-center mt-14 md:mt-14 relative px-4">
            <div class="relative text-center w-full md:w-3/4">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-4 tracking-tight">
                    Le Blog Keytchens
                </h1>
                <p class="text-lg sm:text-xl text-slate-300 max-w-2xl mx-auto leading-relaxed">
                    Les meilleures stratégies pour développer votre restaurant et augmenter vos performances.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ==================== CONTENU ARTICLE ==================== -->
<main class="max-w-screen-xl mx-auto px-4 py-12 lg:py-16">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

        <!-- ===== COLONNE ARTICLE ===== -->
        <article class="lg:col-span-2">
            <!-- Titre -->
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-4 leading-tight">
                <?= e($title) ?>
            </h1>

            <!-- Meta bar -->
            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400 mb-8">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <time datetime="<?= e($date) ?>"><?= formatDateFr($date) ?></time>
                </div>
                <span class="hidden sm:inline text-gray-300">•</span>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <?= $readTime ?> min de lecture
                </div>
            </div>

            <!-- Share top -->
            <div class="flex items-center justify-between mb-8 pb-6 border-b border-gray-100 dark:border-gray-800">
                <span class="text-sm text-gray-500 dark:text-gray-400">Partager :</span>
                <div class="flex items-center gap-2">
                    <button type="button" class="kc-share-btn kc-share-li" data-share="linkedin" title="LinkedIn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.02-3.03-1.85-3.03-1.86 0-2.15 1.45-2.15 2.94v5.66H9.35V9h3.41v1.56h.05c.47-.9 1.63-1.85 3.36-1.85 3.6 0 4.26 2.37 4.26 5.45v6.29ZM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12ZM7.12 20.45H3.56V9h3.56v11.45Z"/></svg>
                    </button>
                    <button type="button" class="kc-share-btn kc-share-fb" data-share="facebook" title="Facebook">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.91h2.54V9.85c0-2.51 1.49-3.9 3.77-3.9 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.44 2.91h-2.34V22c4.78-.76 8.44-4.92 8.44-9.94Z"/></svg>
                    </button>
                    <button type="button" class="kc-share-btn kc-share-ig" data-share="instagram" title="Instagram">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2.5" y="2.5" width="19" height="19" rx="5"/><circle cx="12" cy="12" r="4.2"/><circle cx="17.4" cy="6.6" r="1.1" fill="currentColor" stroke="none"/></svg>
                    </button>
                    <button type="button" class="kc-share-btn kc-share-copy" data-share="copy" title="Copier le lien">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                    </button>
                </div>
            </div>

            <!-- Image principale de l'article (dans l'espace blanc) -->
            <?php if ($image): ?>
            <div class="mb-8 kc-fade-up">
                <img src="<?= e($image) ?>" alt="<?= e($title) ?>"
                     class="w-full h-auto max-h-[480px] object-cover rounded-2xl shadow-lg">
            </div>
            <?php endif; ?>

            <!-- Contenu -->
            <div class="kc-prose max-w-none" id="article-content">
                <?= $content ?>
            </div>

            <!-- Galerie si images supplémentaires -->
            <?php if (!empty($gallery) && is_array($gallery)): ?>
            <div class="mt-10">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">En images</h3>
                <div class="kc-gallery">
                    <?php foreach (array_slice($gallery, 0, 6) as $img): ?>
                    <img src="<?= e($img) ?>" alt="<?= e($title) ?>" loading="lazy" onclick="window.open(this.src, '_blank')">
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- CTA Inline -->
            <div class="kc-cta-box mt-12 mb-8">
                <div class="relative z-10">
                    <h3 class="text-xl font-bold mb-2">Boostez vos commandes dès aujourd'hui</h3>
                    <p class="text-emerald-50 mb-4 text-sm leading-relaxed">
                        Rejoignez les restaurateurs qui augmentent leur chiffre d'affaires sur Uber Eats, Deliveroo et Just Eat avec Keytchens.
                    </p>
                    <a href="https://keytchens.app/signup" class="inline-flex items-center gap-2 bg-white text-emerald-700 px-5 py-2.5 rounded-lg font-semibold text-sm hover:bg-emerald-50 transition">
                        Essai gratuit
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            </div>
        </article>

        <!-- ===== SIDEBAR (sans sommaire) ===== -->
        <aside class="lg:col-span-1">
            <div class="sticky top-24 space-y-6">

                <!-- CTA Sidebar -->
                <div class="kc-cta-box">
                    <div class="relative z-10">
                        <h4 class="font-bold text-lg mb-2">Keytchens</h4>
                        <p class="text-emerald-50 text-sm mb-4">L'outil ultime pour dominer les plateformes de livraison.</p>
                        <a href="/" class="block text-center bg-white text-emerald-700 px-4 py-2 rounded-lg font-semibold text-sm hover:bg-emerald-50 transition">Découvrir</a>
                    </div>
                </div>

                <!-- Articles récents -->
                <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-2xl p-5">
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wide mb-4">Articles récents</h4>
                    <div class="space-y-4">
                        <?php foreach ($recent as $r): ?>
                        <a href="/blog/<?= e($r['slug'] ?? '') ?>" class="group flex gap-3 items-start">
                            <img src="<?= e($r['image'] ?? '') ?>" alt="" class="w-16 h-16 rounded-lg object-cover flex-shrink-0 opacity-90 group-hover:opacity-100 transition">
                            <div>
                                <h5 class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-2 group-hover:text-emerald-600 transition"><?= e($r['title'] ?? '') ?></h5>
                                <time class="text-xs text-gray-400 mt-1 block"><?= formatDateFr($r['date'] ?? null) ?></time>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </aside>
    </div>
</main>

<!-- ==================== NAVIGATION PRÉCÉDENT / SUIVANT ==================== -->
<?php if ($prevArticle || $nextArticle): ?>
<section class="border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50">
    <div class="max-w-screen-xl mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php if ($prevArticle): ?>
            <a href="/blog/<?= e($prevArticle['slug'] ?? '') ?>" class="kc-nav-card group flex items-center gap-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 group-hover:bg-emerald-100 group-hover:text-emerald-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </div>
                <div>
                    <span class="text-xs text-gray-400 uppercase tracking-wide">Article précédent</span>
                    <h4 class="font-semibold text-gray-900 dark:text-white line-clamp-2 group-hover:text-emerald-600 transition"><?= e($prevArticle['title'] ?? '') ?></h4>
                </div>
            </a>
            <?php else: ?>
            <div></div>
            <?php endif; ?>

            <?php if ($nextArticle): ?>
            <a href="/blog/<?= e($nextArticle['slug'] ?? '') ?>" class="kc-nav-card group flex items-center gap-4 md:text-right md:flex-row-reverse">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 group-hover:bg-emerald-100 group-hover:text-emerald-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
                <div>
                    <span class="text-xs text-gray-400 uppercase tracking-wide">Article suivant</span>
                    <h4 class="font-semibold text-gray-900 dark:text-white line-clamp-2 group-hover:text-emerald-600 transition"><?= e($nextArticle['title'] ?? '') ?></h4>
                </div>
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ==================== À LIRE AUSSI ==================== -->
<?php if (!empty($related)): ?>
<section class="bg-white dark:bg-gray-900 py-16 border-t border-gray-100 dark:border-gray-800">
    <div class="max-w-screen-xl mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">À lire aussi</h2>
            <a href="/blog.html" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700 transition">Voir tous les articles →</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <?php foreach ($related as $a): ?>
            <a href="/blog/<?= e($a['slug'] ?? '') ?>" class="group block bg-white dark:bg-gray-800 rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="relative h-48 overflow-hidden">
                    <img src="<?= e($a['image'] ?? '') ?>" alt="<?= e($a['title'] ?? '') ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition duration-300"></div>
                </div>
                <div class="p-5">
                    <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wide"><?= e($a['category'] ?? 'Article') ?></span>
                    <h3 class="mt-2 font-bold text-gray-900 dark:text-white line-clamp-2 group-hover:text-emerald-600 transition"><?= e($a['title'] ?? '') ?></h3>
                    <time class="text-xs text-gray-400 dark:text-gray-500 mt-2 block"><?= formatDateFr($a['date'] ?? null) ?></time>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<div id="footer-placeholder"></div>

<!-- ==================== SCRIPTS ==================== -->
<script src="https://unpkg.com/flowbite@1.4.1/dist/flowbite.js"></script>
<script src="/src/widgets/navbar/navbar.js"></script>
<script src="/src/shared/lang/translate.js"></script>
<script type="module" src="/src/app/index.js"></script>

<script>
    /* ===== Partage ===== */
    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }
        return new Promise((resolve, reject) => {
            try {
                const t = document.createElement('textarea');
                t.value = text; t.style.position = 'fixed'; t.style.opacity = '0';
                document.body.appendChild(t); t.focus(); t.select();
                const ok = document.execCommand('copy');
                document.body.removeChild(t);
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
                window.open('https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(url), 'share-li', 'width=580,height=520');
            } else if (network === 'facebook') {
                window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url), 'share-fb', 'width=580,height=520');
            } else if (network === 'instagram') {
                copyToClipboard(url)
                    .then(() => showToast('Lien copié ! Collez-le dans votre story ou bio Instagram.'))
                    .catch(() => showToast('Impossible de copier le lien.'));
            } else if (network === 'copy') {
                copyToClipboard(url)
                    .then(() => showToast('Lien copié dans le presse-papiers !'))
                    .catch(() => showToast('Impossible de copier le lien.'));
            }
        });
    });
</script>
<script>
    window.KC_LANG_SWITCH_URL = {
        fr: <?= json_encode('/blog/' . $slug) ?>,
        en: <?= json_encode($langSwitchUrl) ?>
    };
</script>
</body>
</html>