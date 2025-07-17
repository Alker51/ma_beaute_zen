<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Produit;
use App\Entity\State;
use App\Entity\User;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use App\Repository\ProduitRepository;
use App\Repository\StateRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/booking', name: 'app_booking_')]
final class BookingController extends AbstractController
{
    private EmailController $emailService;
    public function __construct()
    {
        $this->emailService = new EmailController();
    }

    #[Route(name: 'index', methods: ['GET'])]
    public function index(BookingRepository $bookingRepository): Response
    {
        return $this->render('booking/index.html.twig', [
            'bookings' => $bookingRepository->findAll(),
        ]);
    }

    #[Route(path: '/calendar', name: 'calendar')]
    public function calendar(BookingRepository $bookingRepository): Response
    {
        return $this->render('booking/calendar.html.twig', [
            'bookings' => $bookingRepository->findAll(),
        ]);
    }

    #[Route(path: '/calendarIframe', name: 'calendarIframe')]
    public function calendarIframe(BookingRepository $bookingRepository): Response
    {
        return $this->render('booking/_calendar.html.twig', [
                'bookings' => $bookingRepository->findAll(),
            ]
        );
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, StateRepository $stateRepository, UserRepository $userRepository, ProduitRepository $produitRepository): Response
    {
        $saisie = $request->getSession()->get('saisie_formulaire_rdv', []);
        $booking = new Booking();

        if(!empty($saisie)) {
            $booking->setStart(new \DateTime($saisie['booking']['start']));
            $booking->setEnd(new \DateTime($saisie['booking']['end']));
            $booking->setWorker($userRepository->findOneBy(['id' => $saisie['booking']['worker']]));
            $booking->setTitle($saisie['booking']['title']);
            $booking->setState($stateRepository->findOneBy(['id' => $saisie['booking']['state']]));
            foreach ($saisie['booking']['products'] as $product)
                $booking->addProduct($produitRepository->findOneBy(['id' => $product]));

            $form = $this->createForm(BookingType::class, $booking, [
                'is_admin' => $this->isGranted('ROLE_ADMIN'),
            ]);
            $request->getSession()->remove('saisie_formulaire_rdv');
        } else {
            $form = $this->createForm(BookingType::class, $booking, [
                    'is_admin' => $this->isGranted('ROLE_ADMIN'),
                ]
            );
        }

        $form->handleRequest($request);
        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);

        if ($form->isSubmitted() && $form->isValid()) {
            $booking->setState($stateRepository->findOneBy(['id' => StateController::PENDING_VALIDATION_STATE]));
            $booking->setCustomer($user);

            $bookingGood = $this->checkOverlap($booking->getWorker(), $booking->getStart(), $booking->getEnd(), $entityManager->getRepository(Booking::class), $entityManager->getRepository(User::class));

            if(!$bookingGood) {
                $session = $request->getSession();
                $session->set('saisie_formulaire_rdv', $request->request->all());

                return $this->redirectToRoute('app_error', ['title' => 'Le rendez-vous est indisponible.', 'message' => 'Nous sommes désolé mais le rendez-vous que vous avez demandé n\'est pas disponible, merci de sélectionner un autre pratiquant ou bien un horaire différent.'], Response::HTTP_SEE_OTHER);
            }

            $entityManager->persist($booking);
            $entityManager->flush();

            if($this->checkIfIframe($request))
                return $this->redirectToRoute('app_booking_calendarIframe', [], Response::HTTP_SEE_OTHER);

            return $this->redirectToRoute('app_booking_index', [], Response::HTTP_SEE_OTHER);
        }

        if($this->checkIfIframe($request))
            return $this->render('booking/iframe/newIframe.html.twig', [
                'booking' => $booking,
                'form' => $form,
            ]);

        $allProducts = $entityManager->getRepository(Produit::class)->findAll();

        return $this->render('booking/new.html.twig', [
            'booking' => $booking,
            'form' => $form,
            'all_products' => $allProducts,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Booking $booking, Request $request): Response
    {
        $view = 'booking/show.html.twig';

        if($this->checkIfIframe($request))
            $view = 'booking/iframe/showIframe.html.twig';

        return $this->render($view, [
            'booking' => $booking,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Booking $booking, EntityManagerInterface $entityManager): Response
    {
        $view = 'booking/edit.html.twig';

        if($this->checkIfIframe($request))
            $view = 'booking/iframe/editIframe.html.twig';

        $isAdminEdit = false;
        if($this->isGranted('ROLE_ADMIN')) {
            $view = 'admin/booking/edit.html.twig';
            $isAdminEdit = true;
        }

        $originalEmploye = $booking->getWorker();

        $form = $this->createForm(BookingType::class, $booking, [
                'is_admin' => $this->isGranted('ROLE_ADMIN'),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                // Empêche la modification de l’employé si pas modifié par un admin — on remet la valeur d'origine
                $booking->setWorker($originalEmploye);
            }

            $bookingGood = $this->checkOverlap($booking->getWorker(), $booking->getStart(), $booking->getEnd(), $entityManager->getRepository(Booking::class), $entityManager->getRepository(User::class), $isAdminEdit);

            if(!$bookingGood) {
                return $this->redirectToRoute('app_error', ['title' => 'Le rendez-vous est indisponible.', 'message' => 'Nous sommes désolé mais le rendez-vous que vous avez demandé n\'est pas disponible, merci de sélectionner un autre pratiquant ou bien un horaire différent.'], Response::HTTP_SEE_OTHER);
            }

            $entityManager->flush();

            if($this->checkIfIframe($request))
                return $this->redirectToRoute('app_booking_calendarIframe', [], Response::HTTP_SEE_OTHER);
            return $this->redirectToRoute('app_booking_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render($view, [
            'booking' => $booking,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Booking $booking, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$booking->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($booking);
            $entityManager->flush();
        }

        $fromIframe = $request->request->get('from_iframe', '0'); // '1' ou '0'
        $fromIframeBool = $fromIframe === '1';


        if($fromIframeBool)
            return $this->redirectToRoute('app_booking_calendarIframe', [], Response::HTTP_SEE_OTHER);

        return $this->redirectToRoute('app_booking_index', [], Response::HTTP_SEE_OTHER);
    }

    public function checkOverlap(User|null $worker, \DateTimeInterface $startRaw, \DateTimeInterface $endRaw, BookingRepository $bookingRepository, UserRepository $userRepository, bool $isAnEdit = false): bool
    {
        if($isAnEdit || $worker === null)
            return true;

        try {
            $start = DateTime::createFromInterface($startRaw);
            $end = DateTime::createFromInterface($endRaw);
        } catch (\Exception $e) {
            return false;
        }

        if ($start >= $end) {
            return false;
        }

        $hasOverlap = $bookingRepository->hasOverlappingBooking($worker, $start, $end);

        if ($hasOverlap) {
            return false;
        }

        return true;
    }

    private function checkIfIframe(Request $request) : bool
    {
        $fromIframe = $request->get('from_iframe', '0');
        return $fromIframe === '1';
    }

    #[Route('/validate/{id}', name: 'validate', methods: ['GET'])]
    public function validateBooking(Booking $booking, EntityManagerInterface $entityManager, StateRepository $stateRepository, Request $request) : Response
    {
        $stateBooking = $booking->getState();
        $stateCanBeValidated = [1,5];

        if(in_array($stateBooking->getId(), $stateCanBeValidated)) {

            $booking->setState($stateRepository->findOneBy(['id' => StateController::VALIDATED_STATE]));

            $entityManager->persist($booking);
            $entityManager->flush();
        } else {
            $error = 'Impossible de valider le rendez-vous. L`état du rendez-vous n\'est pas valide. Un Rendez-vous "' . $stateBooking->getName().'" ne peux être validé.';

            return $this->render('home/error.html.twig', [
                'title' => 'Impossible de valider le rendez-vous.',
                'message' => $error,
            ]);
        }

        if($this->checkIfIframe($request))
            return $this->redirectToRoute('app_booking_calendarIframe', [], Response::HTTP_SEE_OTHER);

        return $this->redirectToRoute('app_booking_index', [], Response::HTTP_SEE_OTHER);
    }
}
