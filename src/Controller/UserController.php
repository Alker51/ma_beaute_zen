<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
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

        $emailService = new EmailController();

        if ($form->isSubmitted() && $form->isValid()) {

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);

            $userRepository->save($user, true);

            $result = $emailService->sendMail($user->getEmail(), 'Enregistrement du compte client réussi.', 'Merci pour votre inscription, vous pouvez consulter votre compte des maintenant.');

            return $this->redirectToRoute('app_user_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/delete', name: 'delete', methods: ['GET', 'POST'])]
    public function delete(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);

        if(!empty($_POST['pass_entry'])) {
            if ($user->getPassword() !== $_POST['pass_entry']) {
                throw new AccessDeniedHttpException();
            } else {
                $userRepository->delete($user, true);
                return $this->redirectToRoute('app_user_home', [], Response::HTTP_SEE_OTHER);
            }
        } else {
            return $this->render('user/delete.html.twig');
        }
    }
}
