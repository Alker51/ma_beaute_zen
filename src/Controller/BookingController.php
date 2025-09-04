<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Produit;
use App\Entity\State;
use App\Entity\User;
use App\Form\BookingStep1Type;
use App\Form\BookingStep2Type;
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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
    public function new(Request $request, EntityManagerInterface $entityManager, StateRepository $stateRepository, UserRepository $userRepository, ProduitRepository $produitRepository,UserPasswordHasherInterface $passwordHasher): Response
    {
        $booking = new Booking();
        $saisie = $request->getSession()->get('saisie_formulaire_rdv', []);
        $step = $request->query->getInt('step', 1);

        if(!empty($saisie)) {
            $booking->setStart(new \DateTime($saisie['booking']['start']));
            foreach ($saisie['booking']['products'] as $product)
                $booking->addProduct($produitRepository->findOneBy(['id' => $product]));
        }

        if ($step == 1) {
            $form = $this->createForm(BookingStep1Type::class, $booking, [
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $request->getSession()->set('booking_step1', $data);

                $delay = 0;
                foreach ($data->getProducts() as $product) {
                    $delay += $product->getDelay();
                }

                $end = (clone $data->getStart())->modify("+{$delay} minutes");
                $data->setEnd($end);

                $bookingGood = $this->checkOverlap($data->getWorker(), $data->getStart(), $data->getEnd(), $entityManager->getRepository(Booking::class), $entityManager->getRepository(User::class));

                if(!$bookingGood) {
                    $session = $request->getSession();
                    $session->set('saisie_formulaire_rdv', $request->request->all());

                    return $this->redirectToRoute('app_error', ['title' => 'Le rendez-vous est indisponible.', 'message' => 'Nous sommes désolé mais le rendez-vous que vous avez demandé n\'est pas disponible, merci de sélectionner un autre pratiquant ou bien un horaire différent.'], Response::HTTP_SEE_OTHER);
                }

                // Rediriger vers l'étape 2
                return $this->redirectToRoute('app_booking_new', ['step' => 2]);
            }

            return $this->render('booking/step1.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        if ($step == 2) {
            $step1 = $request->getSession()->get('booking_step1');
            if (!$step1) {
                // Sécurité : revenir à l'étape 1 si étape 2 accédée sans session
                return $this->redirectToRoute('app_booking_new', ['step' => 1]);
            }

            $user = $this->getUser();
            $defaultData = [];
            if ($user) {
                $user = $userRepository->findOneBy(['email' => $user->getUserIdentifier()]);

                $defaultData = [
                    'customerLastName' => $user->getLastName(),
                    'customerFirstName' => $user->getFirstName(),
                    'customerEmail' => $user->getEmail(),
                    'customerPhone' => $user->getPhone(),
                    'customerAdress' => $user->getAdress(),
                    'customerCity' => $user->getCity(),
                    'customerZipCode' => $user->getZipCode(),
                    'customerGender' => $user->getGender(),
                ];
            }

            $form = $this->createForm(BookingStep2Type::class, null, [
                'data_class' => null,
                'is_admin' => $this->isGranted('ROLE_ADMIN'),
                'data' => $defaultData,
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $step2 = $form->getData();
                $title = "RDV de ";
                $booking->setStart($step1->getStart());

                foreach ($step1->getProducts() as $produit) {
                    $booking->addProduct($produitRepository->findOneBy(['id' => $produit]));
                }
                $booking->setEnd($step1->getEnd());
                $customer = [
                    'lastName' => $step2['customerLastName'],
                    'firstName' => $step2['customerFirstName'],
                    'email' => $step2['customerEmail'],
                    'phone' => $step2['customerPhone'],
                    'address' => $step2['customerAdress'],
                    'city' => $step2['customerCity'],
                    'zipCode' => $step2['customerZipCode'],
                    'gender' => $step2['customerGender'],
                ];

                switch ($customer['gender']->getId()) {
                    case 1:
                        $title .= "M.";
                        break;
                    case 2:
                        $title .= "Mme.";
                        break;
                }

                $booking->setTitle($title . $customer['firstName'] . ' ' . $customer['lastName']);
                $user = $userRepository->findOneByMultipleFields(
                    $customer['email'],
                    $customer['phone'],
                    $customer['lastName'],
                    $customer['firstName'],
                );


                if($user === null) {
                    $rdm = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()'), 0, 12);

                    $user = new User(
                        $customer['email'],
                        ['ROLE_USER'],
                        $rdm,
                        $customer['firstName'],
                        $customer['lastName'],
                        $customer['address'],
                        $customer['zipCode'],
                        $customer['city'],
                        $customer['gender'],
                        $customer['phone'],
                        false,
                        true
                    );

                    $userRepository->save($user, true);
                    $title = '[Ma Beauté Zen]Confirmation de création de compte.';
                    $body = '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $title . '</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .header {
            text-align: center;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .header img {
            max-width: 150px;
        }
        .content {
            padding: 20px;
            background-color: #fff;
        }
        .content h1 {
            color: #444;
        }
        .content p {
            margin-bottom: 15px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #6a178f;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }
        .footer {
            text-align: center;
            padding: 10px 0;
            border-top: 1px solid #ddd;
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }
        .footer a {
            color: #6a178f;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center" valign="top">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #f9f9f9;">
                    <!-- Header -->
                    <tr>
                        <td align="center" valign="top" style="padding: 20px;">
                            <img src="https://example.com/logo.png" alt="Logo" style="max-width: 150px;">
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td valign="top" style="padding: 20px; background-color: #fff;">
                            <h1 style="color: #444;">' . $title . '</h1>
                            <p>Cher(e) ' . $user->getFirstName() . ',</p>
                            <p>Votre compte a été créé avec succès.</p>
                            <p><strong>Email :</strong> ' . $user->getEmail() . '</p>
                            <p><strong>Mot de passe temporaire :</strong> ' . $user->getPassword() . '</p>
                            <p><strong>Merci de bien vouloir vous connecter avec votre mot de passe temporaire. Lors de cette premiere connexion, un changement de mot de passe vous sera demandé.</strong></p>
                            <p>Si vous avez des questions ou besoin de modifier des informations, n\'hésitez pas à nous contacter ou a vous rendre sur votre compte.</p>
                            <p><a href="#" style="color: #6a178f; text-decoration: none;">Accéder à mon compte.</a></p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td align="center" valign="top" style="padding: 10px 0; border-top: 1px solid #ddd; margin-top: 20px; font-size: 12px; color: #777;">
                            <p>&copy; 2025 Ma Beauté Zen. Tous droits réservés.</p>
                            <p>
                                <a href="#" style="color: #6a178f; text-decoration: none;">Politique de confidentialité</a> |
                                <a href="#" style="color: #6a178f; text-decoration: none;">Conditions d\'utilisation</a> |
                                <a href="#" style="color: #6a178f; text-decoration: none;">Contactez-nous</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';

                    new EmailController()->sendMail($user->getEmail(), $title, $body, true);
                }

                $pass = $user->getPassword();
                $user->setPassword($passwordHasher->hashPassword($user, $pass));

                $booking->setCustomer($user);

                if(isset($step2['worker']))
                    $booking->setWorker($userRepository->findOneBy(['id' => $step2['worker']]));

                if(isset($step2['state']))
                    $booking->setState($stateRepository->findOneBy(['id' => $step2['state']]));
                else
                    $booking->setState($stateRepository->findOneBy(['id' => StateController::PENDING_STATE]));

                $entityManager->persist($booking);
                $entityManager->flush();

                // Nettoyage session
                $request->getSession()->remove('booking_step1');

                $this->addFlash('success', 'Réservation enregistrée !');

                // TODO : Simplifier le code, beaucoup de code redondant. Mettre les body html dans des fichiers séparé.

                $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::FULL, \IntlDateFormatter::NONE);
                $start = $formatter->format(new DateTime($booking->getStart()->format('Y-m-d')));

                $title = '[Ma Beauté Zen]Confirmation de Rendez-vous.';
                $body = '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>'.$title.'</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .header {
            text-align: center;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .header img {
            max-width: 150px;
        }
        .content {
            padding: 20px;
            background-color: #fff;
        }
        .content h1 {
            color: #444;
        }
        .content p {
            margin-bottom: 15px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007BFF;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }
        .footer {
            text-align: center;
            padding: 10px 0;
            border-top: 1px solid #ddd;
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }
        .footer a {
            color: #007BFF;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center" valign="top">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #f9f9f9;">
                    <!-- Header -->
                    <tr>
                        <td align="center" valign="top" style="padding: 20px;">
                            <img src="https://example.com/logo.png" alt="Logo" style="max-width: 150px;">
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td valign="top" style="padding: 20px; background-color: #fff;">
                            <h1 style="color: #444;">'.$title.'</h1>
                            <p>Cher [Nom du client],</p>
                            <p>Votre rendez-vous a été confirmé avec succès.</p>
                            <p><strong>Date :</strong> '. $start .'</p>
                            <p><strong>Heure :</strong> '.$booking->getStart()->format('H:m').'</p>
                            <p><strong>Lieu :</strong> Salon Ma Beauté Zen</p>
                            <p>Si vous avez des questions ou besoin de modifier votre rendez-vous, n\'hésitez pas à nous contacter.</p>
                            <a href="#" style="display: inline-block; padding: 10px 20px; background-color: #dc3545; color: #fff; text-decoration: none; border-radius: 5px; margin-top: 10px; margin-left: 10px;">Annuler le rendez-vous</a>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td align="center" valign="top" style="padding: 10px 0; border-top: 1px solid #ddd; margin-top: 20px; font-size: 12px; color: #777;">
                            <p>&copy; 2023 Ma Beauté Zen. Tous droits réservés.</p>
                            <p>
                                <a href="#" style="color: #007BFF; text-decoration: none;">Politique de confidentialité</a> |
                                <a href="#" style="color: #007BFF; text-decoration: none;">Conditions d\'utilisation</a> |
                                <a href="#" style="color: #007BFF; text-decoration: none;">Contactez-nous</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
';

                new EmailController()->sendMail($user->getEmail(), $title, $body, true);

                return $this->redirectToRoute('app_booking_index');
            }

            return $this->render('booking/step2.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        // fallback
        return $this->redirectToRoute('app_booking_new', ['step' => 1]);

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


    public function EndHoursCalc($booking): Booking
    {
        $products = $booking->getProducts();
        $delay = 0;
        foreach($products as $product) {
            $delay += $product->getDelay();
        }

        $end = (clone $booking->getStart())->modify("+{$delay} minutes");
        $nbMinRound = 10;
        // Arrondi au quart d’heure supérieur :
        $minute = (int) $end->format('i');
        $modulo = $minute % $nbMinRound;
        if ($modulo > 0) {
            // Ajoute le complément pour atteindre le prochain quart d'heure
            $end->modify('+' . ($nbMinRound - $modulo) . ' minutes');
            // Mets les secondes à zéro
            $end->setTime((int)$end->format('H'), (int)$end->format('i'), 0);
        }

        $booking->setEnd($end);

        return $booking;
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Booking $booking, EntityManagerInterface $entityManager): Response
    {
        $allProducts = $entityManager->getRepository(Produit::class)->findAll();
        $view = 'booking/edit.html.twig';
        $originalEmploye = $booking->getWorker();
        $isAdminEdit = false;

        if($this->checkIfIframe($request))
            $view = 'booking/iframe/editIframe.html.twig';

        if($this->isGranted('ROLE_ADMIN')) {
            $isAdminEdit = true;
        }

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

                $booking = $this->EndHoursCalc($booking);

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
            'all_products' => $allProducts,
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
