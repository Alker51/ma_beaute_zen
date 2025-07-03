<?php

namespace App\Controller\Admin;

use App\Controller\StateController;
use App\Entity\Booking;
use App\Repository\StateRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BookingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Booking::class;
    }

    #[Route('/admin/calendar-iframe', name: 'admin_booking_calendar_iframe')]
    public function adminCalendarIframe(): Response
    {
        return $this->render('admin/calendar_iframe.html.twig');
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
            DateTimeField::new('start', 'Début RDV'),
            DateTimeField::new('end', 'Fin RDV'),
            AssociationField::new('state')
                ->formatValue(function ($value, $entity) {
                $state = $entity->getState();
                $color = new StateController()->getBootstrapColorByState($state->getId());

                return '<span class="badge badge-'.$color.'">' . $state->getName() . '</span>';
            }),
            AssociationField::new('worker', 'Employé(e) attribué(e)')
                ->formatValue(function ($value, $entity) {
                $worker = $entity->getWorker();

                if($worker === null)
                    return null;

                return $worker->getFirstName() . ' ' . $worker->getLastName();
            }),
            AssociationField::new('customer', 'Compte Client')
                ->formatValue(function ($value, $entity) {
                $worker = $entity->getCustomer();

                if($worker === null)
                    return null;

                return $worker->getFirstName() . ' ' . $worker->getLastName();
            }),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $valider = Action::new('valider', 'Valider', 'fa fa-check')
            ->linkToCrudAction('validerBooking', function ($entity) {
                return ['entityId' => $entity->getId()];
            })
            ->addCssClass('validate-btn')
            ->displayIf(static function ($entity) {
                // Afficher uniquement sur les bookings qui ne sont pas déjà validés, par exemple :
                return $entity->getState()->getID() !== '4';
            });

        return $actions
            ->add(Crud::PAGE_DETAIL, $valider)
            ->add(Crud::PAGE_INDEX, $valider);

    }

    public function validerBooking(AdminContext $context, EntityManagerInterface $entityManager,StateRepository $stateRepository): RedirectResponse
    {
        // Si jamais le contexte ne donne pas d'entité !
        $entityId = $context->getRequest()->query->get('entityId');
        if (!$entityId) {
            $this->addFlash('danger', "Rendez-vous non trouvé !");
            return $this->redirectToRoute('admin_booking_index');
        }
        /** @var Booking|null $booking */
        $booking = $entityManager->getRepository(Booking::class)->find($entityId);
        if (!$booking) {
            $this->addFlash('danger', "Rendez-vous introuvable !");
            return $this->redirectToRoute('admin_booking_index');
        }




        $stateValide = 4;
        $booking->setState($stateRepository->findOneBy(['id' => $stateValide]));;

        $entityManager->persist($booking);
        $entityManager->flush();

        $this->addFlash('success', 'Le rendez-vous a été validé.');

        return $this->redirectToRoute('admin_booking_index');
    }

}
