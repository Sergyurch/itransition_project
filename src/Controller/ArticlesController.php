<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use App\Form\ArticleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\FileUploader;
use Knp\Component\Pager\PaginatorInterface;

class ArticlesController extends AbstractController
{
    #[Route('/')]
    public function indexNoLocale(): Response
    {
        return $this->redirectToRoute('app_articles', ['_locale' => 'ru']);
    }

    #[Route('/{_locale<%app.supported_locales%>}/', methods: ['GET'], name: 'app_articles')]
    public function index(Request $request, PaginatorInterface $paginator, ArticleRepository $articleRepository): Response
    {
        $allArticlesQuery = $articleRepository->createQueryBuilder('a')->orderBy('a.created_at', 'DESC')->getQuery();
        $bestArticles = $articleRepository->getBestArticles();

        $articles = $paginator->paginate(
            $allArticlesQuery,
            $request->query->getInt('page', 1),
            6
        );
        
        return $this->render('articles/index.html.twig', [
            'articles' => $articles,
            'bestArticles' => $bestArticles
        ]);
    }

    #[Route('/{_locale<%app.supported_locales%>}/article/{id<\d+>}', methods: ['GET'], name: 'app_article_show')]
    public function show($id, CommentRepository $commentsRepository, ArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->find($id);
        $comments = $commentsRepository->findBy(['article' => $id], ['created_at' => 'ASC']);
        $category = ($article->getCategory() == 'Фильмы') ? 'movies' : ( ($article->getCategory() == 'Книги') ? 'books' : 'games' );
        
        return $this->render('articles/show.html.twig', [
            'article' => $article,
            'comments' => $comments,
            'category' => $category
        ]);
    }

    #[Route('/{_locale<%app.supported_locales%>}/articles/{category<movies|books|games>}', methods: ['GET'], name: 'app_articles_category')]
    public function categoryShow(string $category, Request $request, PaginatorInterface $paginator, ArticleRepository $articleRepository): Response
    {
        $category = ($category == 'movies') ? 'Фильмы' : ( ($category == 'books') ? 'Книги' : 'Игры' );
        $articlesQuery = $articleRepository->findByCategory($category);
        
        $articles = $paginator->paginate(
            $articlesQuery,
            $request->query->getInt('page', 1),
            6
        );
        
        return $this->render('articles/category.html.twig', [
            'articles' => $articles,
            'category' => $category
        ]);
    }

    #[Route('/{_locale<%app.supported_locales%>}/article/create/{userId<\d+>}', name: 'app_article_create')]
    public function create($userId, Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader, UserRepository $userRepository): Response
    {

        if ( $userId == $this->getUser()->getId() || $this->isGranted('ROLE_ADMIN') ) {
            $article = new Article();
            $form = $this->createForm(ArticleType::class, $article);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $articleImageFile = $form->get('image_path')->getData();
                $filePath = $fileUploader->uploadImageToCloudinary($articleImageFile, 'images');
                $article->setImagePath($filePath);
                $article->setCreatedAt(new \DateTime("now"));
                $article->setAuthor($userRepository->find($userId));
                
                $entityManager->persist($article);
                $entityManager->flush();
            
                return $this->redirectToRoute('app_profile_articles', ['id' => $userId]);
            }

            return $this->render('articles/create.html.twig', [
                'articleCreateForm' => $form->createView()
            ]);
        } else {
            return $this->redirectToRoute('app_login');
        }
    }

    #[Route('/{_locale<%app.supported_locales%>}/article/edit/{id<\d+>}', name: 'app_article_edit')]
    public function edit($id, Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader, ArticleRepository $articleRepository): Response
    {

        $article = $articleRepository->find($id);

        if ( $article->getAuthor()->getId() == $this->getUser()->getId() || $this->isGranted('ROLE_ADMIN') ) {
            $form = $this->createForm(ArticleType::class, $article);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                
                $image = $form->get('image_path')->getData();
    
                if ($image) {
                    $fileUploader->destroyImageCloudinary($article->getImagePath());
                    $newImagePath = $fileUploader->uploadImageToCloudinary($image, 'images');
                    $article->setImagePath($newImagePath);
                }
    
                $entityManager->persist($article);
                $entityManager->flush();
                
                return $this->redirectToRoute('app_profile_articles', ['id' => $article->getAuthor()->getId()]);
            }

            return $this->render('articles/edit.html.twig', [
                'articleEditForm' => $form->createView()
            ]);
        } else {
            return $this->redirectToRoute('app_login');
        }

    }

    #[Route('/{_locale<%app.supported_locales%>}/article/delete/{id<\d+>}', name: 'app_article_delete')]
    public function delete($id, EntityManagerInterface $entityManager, FileUploader $fileUploader, ArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->find($id);
        
        if ( $article->getAuthor()->getId() == $this->getUser()->getId() || $this->isGranted('ROLE_ADMIN') ) {
            $fileUploader->destroyImageCloudinary($article->getImagePath());
            $entityManager->remove($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_profile_articles', ['id' => $this->getUser()->getId()]);
        } else {
            return $this->redirectToRoute('app_login');
        }
    }

    #[Route('/{_locale<%app.supported_locales%>}/articles/search', name: 'app_articles_search')]
    public function search(Request $request, ArticleRepository $articleRepository, PaginatorInterface $paginator): Response
    {
        $needle = $request->query->get('text');
        $needle = preg_replace('/\s+/', '|', trim($needle));
        $needle = mb_strtolower($needle);
                
        $searchQuery = $articleRepository->getSearchQuery($needle);
        
        $articles = $paginator->paginate(
            $searchQuery,
            $request->query->getInt('page', 1),
            6
        );
        
        return $this->render('articles/search.html.twig', [
            'articles' => $articles,
        ]);
    }

}
