<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Comment;
use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;

class CommentsController extends AbstractController
{
    #[Route('/{_locale<%app.supported_locales%>}/comment/create', name: 'app_comment_create')]
    public function createComment(Request $request, UserRepository $userRepository, ArticleRepository $articleRepository, EntityManagerInterface $entityManager, CommentRepository $commentRepository): Response
    {
        $comment = new Comment();
        $comment->setText($request->get('text'));
        $comment->setUser($userRepository->find($request->get('userId')));
        $comment->setArticle($articleRepository->find($request->get('articleId')));
        $comment->setCreatedAt(new \DateTime("now"));

        $entityManager->persist($comment);
        $entityManager->flush();

        $comments = $commentRepository->findBy(['article' => $request->get('articleId')], ['created_at' => 'ASC']);
        $data = [];
        
        foreach ($comments as $comment) {
            $data[] = array(
                'commentText' => $comment->getText(),
                'commentDate' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
                'userName' => $comment->getUser()->getFullName(),
                'userPhoto' => $comment->getUser()->getProfileImage()
            );
        }
        
        return new JsonResponse($data);
    }
}
