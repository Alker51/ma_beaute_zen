<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/user', name: 'app_user_')]

final class UserController extends AbstractController
{
    private $logger;
    private $tokenStorage;
    private $emailService;

    public function __construct(LoggerInterface $logger, TokenStorageInterface $tokenStorage)
    {
        $this->logger = $logger;
        $this->tokenStorage = $tokenStorage;
        $this->emailService = new EmailController();
    }

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

            $this->emailService->sendMail($user->getEmail(), 'Enregistrement du compte client réussi.', 'Merci pour votre inscription, vous pouvez consulter votre compte des maintenant.');

            return $this->redirectToRoute('app_user_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/delete', name: 'delete', methods: ['GET', 'POST'])]
    public function delete(UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, Request $request): Response
    {
        $passEntry = "";
        if(!empty($_POST['pass_entry']))
            $passEntry = $_POST['pass_entry'];

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);

        if (!empty($passEntry)) {
            if (!$passwordHasher->isPasswordValid($user, $passEntry)) {
                throw new AccessDeniedHttpException();
            } else {
                $this->emailService->sendMail($user->getEmail(), 'Suppression du compte client réussi.', 'Nous sommes désolé de vous voir partir, nous espérons vous revoir bientôt.');

                $userRepository->delete($user, true);
                $request->getSession()->invalidate();
                $this->tokenStorage->setToken(null);
                return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
            }
        } else {
            return $this->render('user/delete.html.twig');
        }
    }
}
