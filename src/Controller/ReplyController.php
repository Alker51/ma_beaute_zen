<?php

namespace App\Controller;

use App\Entity\Reply;
use App\Form\ReplyType;
use App\Repository\ContactRepository;
use App\Repository\ReplyRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReplyController extends AbstractController
{
    private EmailController $emailService;

    public function __construct()
    {
        $this->emailService = new EmailController();
    }

    #[Route('/reply/{contactId}', name: 'app_reply')]
    public function index(int $contactId, ReplyRepository $replyRepository, ContactRepository $contactRepository, UserRepository $userRepository,Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $reply = new Reply();
        $form = $this->createForm(ReplyType::class, $reply);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contact = $contactRepository->find($contactId);
            if (!$contact) {
                throw $this->createNotFoundException('Contact non trouvé');
            }

            $reply->setReplayDate(new \DateTime('now'));
            $reply->setContactReference($contact);

            $contact->setEditedTime(new \DateTime('now'));

            $contactRepository->save($contact, true);
            $replyRepository->save($reply, true);

            $body= '<div>
                <h1>Vous avez reçu une reponse à votre demande</h1><br>
                <h2>'. $reply->getMessage().'</h2><br>
                <div>'.$reply->getReplayDate()->format('d/m/y H:i').'</div><br><br>
                <div>Bonne journée</div>
            </div>';

            $this->emailService->sendMail(
                $contact->getUser()->getEmail(),
                'Votre message a été envoyé.',
                '<!DOCTYPE html><html lang="fr">'.$body.'</html>');


            return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reply/index.html.twig', [
            'form' => $form,
        ]);
    }
}
