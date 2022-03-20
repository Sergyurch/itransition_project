<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use App\Form\ArticleCreateFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\FileUploader;

class ArticlesController extends AbstractController
{
    private $articleRepository;
    private $userRepository;

    public function __construct(ArticleRepository $articleRepository, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->articleRepository = $articleRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/', methods: ['GET'], name: 'app_articles')]
    public function index(): Response
    {
        $articles = $this->articleRepository->findBy([], ['created_at' => 'DESC']);
        
        return $this->render('articles/index.html.twig', [
            'articles' => $articles
        ]);
    }

    #[Route('/article/{id<\d+>}', methods: ['GET'], name: 'app_article_show')]
    public function show($id): Response
    {
        $article = $this->articleRepository->find($id);
        
        return $this->render('articles/show.html.twig', [
            'article' => $article
        ]);
    }

    #[Route('/article/create/{userId<\d+>}', name: 'app_article_create')]
    public function create($userId, Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {

        if ( $userId == $this->getUser()->getId() || $this->isGranted('ROLE_ADMIN') ) {
            $article = new Article();
            $form = $this->createForm(ArticleCreateFormType::class, $article);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $articleImageFile = $form->get('image_path')->getData();
                $filePath = $fileUploader->uploadImageToCloudinary($articleImageFile, 'images');
                $article->setImagePath($filePath);
                $article->setCreatedAt(new \DateTime("now"));
                $article->setAuthor($this->userRepository->find($userId));
                
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

    #[Route('/article/edit/{id<\d+>}', name: 'app_article_edit')]
    public function edit($id, Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {

        $article = $this->articleRepository->find($id);

        if ( $article->getAuthor()->getId() == $this->getUser()->getId() || $this->isGranted('ROLE_ADMIN') ) {
            $form = $this->createForm(ArticleCreateFormType::class, $article);
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

    #[Route('/article/delete/{id<\d+>}', name: 'app_article_delete')]
    public function delete($id, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        $article = $this->articleRepository->find($id);
        
        if ( $article->getAuthor()->getId() == $this->getUser()->getId() || $this->isGranted('ROLE_ADMIN') ) {
            $fileUploader->destroyImageCloudinary($article->getImagePath());
            $entityManager->remove($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_profile_articles', ['id' => $this->getUser()->getId()]);
        } else {
            return $this->redirectToRoute('app_login');
        }
    }
}
