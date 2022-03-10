<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticlesController extends AbstractController
{
    private $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    #[Route('/', methods: ['GET'], name: 'app_articles')]
    public function index(): Response
    {
        $articles = $this->articleRepository->findAll();
        // dd($articles);

        return $this->render('articles/index2.html.twig', [
            'controller_name' => 'ArticlesController',
            'articles' => $articles
        ]);
    }

    #[Route('/article/{id}', methods: ['GET'], name: 'app_article_show')]
    public function show($id): Response
    {
        $article = $this->articleRepository->find($id);
        
        return $this->render('articles/show.html.twig', [
            'article' => $article
        ]);
    }
}
