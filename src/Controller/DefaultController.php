<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Repository\ArticleRepository;
use App\Repository\CommentaryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'show_home', methods: ['GET'])]
    public function showHome(ArticleRepository $repository): Response
    {
        $articles = $repository->findBy(['deletedAt' => null]);

        return $this->render('default/show_home.html.twig', [
            'articles' => $articles
        ]);
    }

    #[Route('/voir-articles/{alias}', name: 'show_articles_from_cat', methods:['GET'])]
    public function showArticlesFromCategory(Category $category, ArticleRepository $articleRepository):Response
    {
        $articles = $articleRepository->findBy([
            'deletedAt' => null,
            'category' => $category->getId()
        ]);

        return $this->render('default/show_articles_from_cat.html.twig', [
            'articles' => $articles,
            'category' => $category
        ]);
    }


    #[Route('/{cat_alias}/{art_alias}_{id}.html', name:'show_article', methods:['GET'])]
    public function showArticle(Article $article, CommentaryRepository $commentaryRepository):Response
    {
        $commentaries = $commentaryRepository->findBy(['deletedAt' => null, 'article' => $article->getId()]);

        return $this->render('default/show_article.html.twig', [
            'article' => $article,
            'commentaries' => $commentaries
        ]);
    }
}
