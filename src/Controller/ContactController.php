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
use function PHPUnit\Framework\throwException;

#[Route('/contact', name: 'app_contact_')]
final class ContactController extends AbstractController
{
    private CONST int PENDING_STATE = 1;
    private CONST int FINISH_STATE = 2;
    private CONST int DISCONTINUED_STATE = 3;


    private EmailController $emailService;
    public function __construct()
    {
        $this->emailService = new EmailController();
    }
    //TODO : Voir par la suite pour avoir une liste des contacts en cours pour un User.
    #[Route('/index', name: 'index')]
    public function index(ContactRepository $contactRepository, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
        $contacts = $contactRepository->findBy(['user' => $user], ['editedTime' => 'DESC']);

        return $this->render('contact/index.html.twig', [
            'title' => 'Liste des demandes en cours',
            'contacts' => $contacts
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

            return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contact/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/detail/{id}', name: 'detail', methods: ['GET'])]
    public function show(Contact $contact, ContactRepository $contactRepository, UserRepository $userRepository, StateRepository $stateRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $replies = $contact->getReplies();
        $replies = $replies->toArray();

        usort($replies, function($a, $b) {
            return $b->getReplayDate() <=> $a->getReplayDate();
        });

        return $this->render('contact/detail.html.twig', [
            'contact' => $contact,
            'contactReplies' => $replies,
        ]);
    }

    #[Route('/solve/{id}', name: 'solve', methods: ['GET'])]
    public function solve(Contact $contact, ContactRepository $contactRepository, UserRepository $userRepository, StateRepository $stateRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        if($contact->getState()->getId() !== $this::PENDING_STATE) {
            throwException(new \Exception('Le contact n\'est pas en cours de traitement.'));
        }

        $contact->setState($stateRepository->findOneBy(['id' => $this::FINISH_STATE]));
        $contactRepository->save($contact, true);

        $body= '<div>
                <h1>Cloture de votre demande</h1><br>
                <div>Nous vous informons que votre demande est cloturée.</div><br><br>
                <div>Nous espérons que les réponses obtenue sont à la hauteur de vos espérance. Bonne journée</div>
            </div>';

        $this->emailService->sendMail(
            $contact->getUser()->getEmail(),
            'Cloture de votre demande',
            '<!DOCTYPE html><html>'.$body.'</html>');

        $old_route = $request->attributes->get('_route');
        return $this->redirectToRoute($old_route);
    }

    #[Route('/discontinue/{id}', name: 'discontinue', methods: ['GET'])]
    public function discontinue(Contact $contact, ContactRepository $contactRepository, UserRepository $userRepository, StateRepository $stateRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        if ($contact->getState()->getId() !== $this::PENDING_STATE) {
            throwException(new \Exception('Le contact n\'est pas en cours de traitement.'));
        }

        $contact->setState($stateRepository->findOneBy(['id' => $this::DISCONTINUED_STATE]));
        $contactRepository->save($contact, true);

        $body= '<div>
                <h1>Cloture de votre demande</h1><br>
                <div>Nous vous informons que votre demande est abandonnée.</div><br><br>
                <div>Nous sommes désolé de n\'avoir pu vous répondre favorablement. Bonne journée</div>
            </div>';

        $this->emailService->sendMail(
            $contact->getUser()->getEmail(),
            'Cloture de votre demande',
            '<!DOCTYPE html><html>'.$body.'</html>');

        $old_route = $request->attributes->get('_route');
        return $this->redirectToRoute($old_route);
    }
}
