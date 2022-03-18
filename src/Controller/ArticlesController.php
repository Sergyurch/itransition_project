<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
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

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    #[Route('/', methods: ['GET'], name: 'app_articles')]
    public function index(): Response
    {
        $articles = $this->articleRepository->findAll();
        // dd($articles);

        return $this->render('articles/index.html.twig', [
            'controller_name' => 'ArticlesController',
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

    #[Route('/article/create', name: 'app_article_create')]
    public function create(Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleCreateFormType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $articleImageFile = $form->get('image_path')->getData();
            $filePath = $fileUploader->uploadImageToCloudinary($articleImageFile, 'images');
            $article->setImagePath($filePath);
            $article->setCreatedAt(new \DateTime("now"));
            $article->setAuthor($this->getUser());
            
            $entityManager->persist($article);
            $entityManager->flush();
        
            return $this->redirectToRoute('app_profile', ['id' => $this->getUser()->getId()]);
        }

        return $this->render('articles/create.html.twig', [
            'articleCreateForm' => $form->createView()
        ]);
    }

    #[Route('/article/edit/{id<\d+>}', name: 'app_article_edit')]
    public function edit($id, Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {

        $article = $this->articleRepository->find($id);

        if ( $article->getAuthor()->getId() == $this->getUser()->getId() ) {
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
        $fileUploader->destroyImageCloudinary($article->getImagePath());
        $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('app_profile_articles', ['id' => $this->getUser()->getId()]);
    }
}
