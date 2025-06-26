<?php

namespace App\Controller\Admin;

use App\Controller\StateController;
use App\Entity\Booking;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
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

                return $worker->getFirstName() . ' ' . $worker->getLastName();
            }),
            AssociationField::new('customer', 'Compte Client')
                ->formatValue(function ($value, $entity) {
                $worker = $entity->getWorker();

                return $worker->getFirstName() . ' ' . $worker->getLastName();
            }),
        ];
    }
}
