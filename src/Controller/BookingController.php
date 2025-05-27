<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use App\Repository\StateRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/booking')]
final class BookingController extends AbstractController
{
    private EmailController $emailService;
    public function __construct()
    {
        $this->emailService = new EmailController();
    }

    #[Route(name: 'app_booking_index', methods: ['GET'])]
    public function index(BookingRepository $bookingRepository): Response
    {
        return $this->render('booking/index.html.twig', [
            'bookings' => $bookingRepository->findAll(),
        ]);
    }

    #[Route(path: '/calendar', name: 'app_booking_calendar')]
    public function calendar(): Response
    {
        return $this->render('booking/calendar.html.twig');
    }

    #[Route('/new', name: 'app_booking_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, StateRepository $stateRepository, UserRepository $userRepository): Response
    {
        $booking = new Booking();
        $form = $this->createForm(BookingType::class, $booking, [
                'is_admin' => $this->isGranted('ROLE_ADMIN'),
            ]
        );
        $form->handleRequest($request);
        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);

        if ($form->isSubmitted() && $form->isValid()) {
            $booking->setState($stateRepository->findOneBy(['id' => StateController::PENDING_VALIDATION_STATE]));
            $booking->setCustomer($user);
            $entityManager->persist($booking);
            $entityManager->flush();

            return $this->redirectToRoute('app_booking_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('booking/new.html.twig', [
            'booking' => $booking,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_booking_show', methods: ['GET'])]
    public function show(Booking $booking): Response
    {
        return $this->render('booking/show.html.twig', [
            'booking' => $booking,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_booking_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Booking $booking, EntityManagerInterface $entityManager): Response
    {
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

            $entityManager->flush();
            return $this->redirectToRoute('app_booking_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('booking/edit.html.twig', [
            'booking' => $booking,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_booking_delete', methods: ['POST'])]
    public function delete(Request $request, Booking $booking, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$booking->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($booking);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_booking_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/booking/check-overlap', name: 'booking_check_overlap', methods: ['POST'])]
    public function checkOverlap(Request $request, BookingRepository $repository, UserRepository $userRepository): JsonResponse
    {
        $workerId = $request->request->get('worker');
        $start = $request->request->get('start');
        $end = $request->request->get('end');

        $worker = $userRepository->find($workerId);
        if (!$worker || !$start || !$end) {
            return new JsonResponse(['overlap' => false, 'success' => false], 400);
        }

        $hasOverlap = $repository->hasOverlappingBooking(
            $worker,
            new \DateTime($start),
            new \DateTime($end)
        );

        return new JsonResponse(['overlap' => $hasOverlap]);
    }

}
