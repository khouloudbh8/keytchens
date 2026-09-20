<?php

namespace App\Controller;

use App\Document\Article;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogShareController extends AbstractController
{
    /**
     * Sert exactement le même fichier public/blog.html, mais avec les
     * balises <meta> og:title / og:description / og:image / og:url
     * remplacées par celles de l'article demandé.
     *
     * Aucun template Twig, aucun nouveau dossier : on lit le fichier HTML
     * existant tel quel et on fait un simple remplacement de texte dessus
     * avant de le renvoyer.
     */
    #[Route('/partage/{slug}', name: 'blog_share', methods: ['GET'])]
    public function share(string $slug, DocumentManager $dm): Response
    {
        // 1. Récupère l'article correspondant au slug, exactement comme
        //    ArticleController::show() le fait déjà pour /api/articles/{slug}
        $article = $dm->getRepository(Article::class)->findOneBy(['slug' => $slug]);

        // 2. Charge le HTML existant de blog.html tel quel
        $htmlPath = $this->getParameter('kernel.project_dir') . '/public/blog.html';
        $html = file_get_contents($htmlPath);

        if ($html === false) {
            throw $this->createNotFoundException('blog.html introuvable.');
        }

        // 3. Si l'article existe, on remplace les balises OG par défaut
        //    par celles de cet article précis.
        if ($article) {
            $title = htmlspecialchars($article->getTitle(), ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars($article->getExcerpt(), ENT_QUOTES, 'UTF-8');
            $image = htmlspecialchars($article->getImage(), ENT_QUOTES, 'UTF-8');
            $shareUrl = $this->generateUrl(
                'blog_share',
                ['slug' => $slug],
                \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL
            );

            // Remplace le <title> de l'onglet
            $html = preg_replace(
                '/<title>.*?<\/title>/s',
                '<title>' . $title . '</title>',
                $html,
                1
            );

            // Remplace og:title (et le title HTML classique)
            $html = str_replace(
                'content="Blog Keytchens - Conseils pour votre restaurant"',
                'content="' . $title . '"',
                $html
            );

            // Remplace les deux occurrences de description (meta description + og:description)
            $html = str_replace(
                "Conseils, actualités et stratégies pour optimiser vos livraisons Uber Eats, Deliveroo et booster le chiffre d'affaires de votre restaurant.",
                $description,
                $html
            );
            $html = str_replace(
                "Conseils, actualités et stratégies pour optimiser vos livraisons et booster votre chiffre d'affaires.",
                $description,
                $html
            );

            // Remplace og:image
            $html = str_replace(
                'https://www.keytchens.com/images/keytchens-og-default.png',
                $image,
                $html
            );

            // Remplace og:url / canonical pour pointer vers cette URL de partage précise
            $html = str_replace(
                'https://www.keytchens.com/blog.html',
                $shareUrl,
                $html
            );
        }

        return new Response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
}