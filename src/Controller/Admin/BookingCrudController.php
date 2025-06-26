<?php

namespace App\Controller\Admin;

use App\Entity\Booking;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
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
            DateField::new('start'),
            DateField::new('start'),
            AssociationField::new('state'),
            AssociationField::new('worker'),
            AssociationField::new('customer'),
        ];
    }
}
