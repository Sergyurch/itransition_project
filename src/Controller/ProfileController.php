<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\EditProfileType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\FileUploader;

class ProfileController extends AbstractController
{
    #[Route('/profile/{id}', name: 'app_profile')]
    public function index(int $id, UserRepository $userRepository): Response
    {
        
        if ( $id == $this->getUser()->getId() || $this->isGranted('ROLE_ADMIN') ) {
            return $this->render('profile/index.html.twig', [
                'user' => $userRepository->find($id)
            ]);
        } else {
            return $this->redirectToRoute('app_login');
        }

    }

    #[Route('/profile/edit/{id}', name: 'app_profile_edit')]
    public function edit(int $id, UserRepository $userRepository, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        
        if ( $id == $this->getUser()->getId() || $this->isGranted('ROLE_ADMIN') ) {
            $user = $userRepository->find($id);
            $form = $this->createForm(EditProfileType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ( $form->get('plainPassword')->getData() != null ) { 
                    $user->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            $form->get('plainPassword')->getData()
                        )
                    );
                }
    
                $profileImageFile = $form->get('profile_image')->getData();
    
                if ($profileImageFile) {
                    $fileUploader->destroyImageCloudinary($user->getProfileImage());
                    $newFilename = $fileUploader->uploadImageToCloudinary($profileImageFile, 'profile');
                    $user->setProfileImage($newFilename);
                }
    
                $entityManager->persist($user);
                $entityManager->flush();
                    
                return $this->redirectToRoute('app_profile', ['id' => $user->getId()]);
            }

            return $this->render('profile/edit.html.twig', [
                'editForm' => $form->createView(),
                'user' => $user
            ]);
        } else {
            return $this->redirectToRoute('app_login');
        }

    }

    #[Route('/profile/articles/{id}', name: 'app_profile_articles')]
    public function showArticles(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);

        if ( $id == $this->getUser()->getId() || $this->isGranted('ROLE_ADMIN') ) {
            return $this->render('profile/articles.html.twig', [
                'articles' => $user->getArticles(),
                'user' => $user
            ]);
        } else {
            return $this->redirectToRoute('app_login');
        }

    }

}
