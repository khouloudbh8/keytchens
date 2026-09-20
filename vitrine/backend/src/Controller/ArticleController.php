<?php

namespace App\Controller;

use App\Document\Article;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/articles')]
class ArticleController extends AbstractController
{
    #[Route('/{slug}/translation', methods: ['GET'])]
public function translation(string $slug, DocumentManager $dm): JsonResponse
{
    $article = $dm->getRepository(Article::class)->findOneBy(['slug' => $slug]);

    if (!$article) {
        return $this->json(['slug' => null]);
    }

    $targetLang = $article->getLang() === 'fr' ? 'en' : 'fr';

    $sibling = $dm->getRepository(Article::class)->findOneBy([
        'translationKey' => $article->getTranslationKey(),
        'lang' => $targetLang,
        'status' => 'published',
    ]);

    return $this->json(['slug' => $sibling?->getSlug()]);
}
    #[Route('', methods: ['GET'])]
    public function list(Request $request, DocumentManager $dm): JsonResponse
    {
        $lang = $request->query->get('lang', 'fr');

        $articles = $dm->getRepository(Article::class)
            ->findBy(['status' => 'published', 'lang' => $lang], ['date' => 'desc']);

        return $this->json(array_map(fn(Article $a) => $this->serializeList($a), $articles));
    }

    #[Route('/{slug}', methods: ['GET'])]
    public function show(string $slug, DocumentManager $dm): JsonResponse
    {
        $article = $dm->getRepository(Article::class)->findOneBy(['slug' => $slug]);

        if (!$article) {
            return $this->json(['error' => 'Article introuvable'], 404);
        }

        return $this->json($this->serializeFull($article));
    }

    private function serializeList(Article $a): array
    {
        return [
            'slug'    => $a->getSlug(),
            'title'   => $a->getTitle(),
            'excerpt' => $a->getExcerpt(),
            'date'    => $a->getDate(),
            'image'   => $a->getImage(),
        ];
    }

    private function serializeFull(Article $a): array
    {
        return [
            'slug'     => $a->getSlug(),
            'title'    => $a->getTitle(),
            'category' => $a->getCategory(),
            'date'     => $a->getDate(),
            'image'    => $a->getImage(),
            'content'  => $a->getContent(),
        ];
    }
}