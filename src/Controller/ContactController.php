<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use App\Repository\StateRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/contact', name: 'app_contact_')]
final class ContactController extends AbstractController
{
    private CONST int PENDING_STATE = 1;
    private CONST int FINISH_STATE = 2;
    private CONST int ABANDONNED_STATE = 3;


    private EmailController $emailService;
    public function __construct()
    {
        $this->emailService = new EmailController();
    }
    //TODO : Voir par la suite pour avoir une liste des contacts en cours pour un User.
    #[Route('/index', name: 'index')]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig', [
            'controller_name' => 'ContactController',
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, ContactRepository $contactRepository, UserRepository $userRepository, StateRepository $stateRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);

        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $body= '<div>
                <h1>Résumé de votre contact</h1><br>
                <h2>'. $contact->getTitle().'</h2><br>
                <div>'.$contact->getDetail().'</div><br><br>
                <div>Nous vous repondrons le plus rapidement possible. Bonne journée</div>
            </div>';

            $contact->setCreationDate(new \DateTime('now'));
            $contact->setState($stateRepository->findOneBy(['id' => $this::PENDING_STATE]));
            $contact->setUser($user);
            $contact->setEditedTime($contact->getCreationDate());

            $this->emailService->sendMail(
                $user->getEmail(),
                'Votre message a été envoyé.',
                '<!DOCTYPE html><html>'.$body.'</html>');

            $contactRepository->save($contact, true);

            return $this->redirectToRoute('app_user_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contact/new.html.twig', [
            'form' => $form,
        ]);
    }
}
