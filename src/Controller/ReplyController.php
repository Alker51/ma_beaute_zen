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
        $reply->setAuthor($userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]));

        $form = $this->createForm(ReplyType::class, $reply);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contact = $contactRepository->find($contactId);
            if (!$contact) {
                throw $this->createNotFoundException('Contact non trouvé');
            }

            $reply->setReplayDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $reply->setContactReference($contact);

            $contact->setEditedTime(new \DateTime('now', new \DateTimeZone('Europe/Paris')));

            $contactRepository->save($contact, true);
            $replyRepository->save($reply, true);

            $body= '<div>
                <h1>Vous avez reçu une reponse de ' . $reply->getAuthorString() . '</h1><br>
                <h2>'. $reply->getMessage().'</h2><br>
                <div>'. $reply->getReplayDate()->format('d/m/Y H:i'). '</div><br><br>
                <div>Bonne journée</div>
            </div>';

            $this->emailService->sendMail(
                $contact->getUser()->getEmail(),
                'Ma Beauté Zen - Vous avez reçu une nouvelle reponse de ' . $reply->getAuthorString(),
                '<!DOCTYPE html><html lang="fr">'.$body.'</html>');

            $old_route = $request->attributes->get('_route');
            return $this->redirectToRoute($old_route);
        }

        return $this->render('reply/index.html.twig', [
            'form' => $form,
        ]);
    }
}
