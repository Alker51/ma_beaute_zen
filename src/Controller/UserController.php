<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/user', name: 'app_user_')]

final class UserController extends AbstractController
{
    #[Route('/', name: 'home')]

    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);

            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/delete', name: 'delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, User $user, UserPasswordHasherInterface $passwordHasher): Response
    {
        $passwordEntry = '';

        if(!$passwordHasher->isPasswordValid($user, $passwordEntry)) {
            throw new AccessDeniedHttpException();
        }
    }
}
