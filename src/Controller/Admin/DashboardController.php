<?php

namespace App\Controller\Admin;

use App\Entity\Booking;
use App\Entity\Contact;
use App\Entity\Image;
use App\Entity\Produit;
use App\Entity\Tax;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }
    public function index(): Response
    {

        return $this->redirectToRoute('admin_user_index');
        //return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // 1.1) If you have enabled the "pretty URLs" feature:
        // return $this->redirectToRoute('admin_user_index');
        //
        // 1.2) Same example but using the "ugly URLs" that were used in previous EasyAdmin versions:
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirectToRoute('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Ma Beauté Zen');
    }

    public function configureMenuItems(): iterable
    {
        //yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToRoute('Rendez-vous', 'fa fa-calendar-days', 'admin_booking_calendar_iframe');

        yield MenuItem::section('Gestion Clients');
        yield MenuItem::linkToCrud('Compte client', 'fa fa-user', User::class);
        yield MenuItem::linkToCrud('Demande client', 'fa fa-regular fa-circle-question', Contact::class);

        yield MenuItem::section('Gestion Produits & Services');
        yield MenuItem::linkToCrud('Produits', 'fa fa-box', Produit::class);
        yield MenuItem::linkToCrud('Taxe', 'fa fa-percent', Tax::class);
        yield MenuItem::linkToCrud('Image', 'fa fa-images', Image::class);

        yield MenuItem::section('Administration');
        yield MenuItem::linkToRoute('Retour à l\'accueil', 'fa fa-door-open', 'app_home')->setCssClass('menu-item-home');
        yield MenuItem::linkToLogout('Déconnexion', 'fa-solid fa-right-from-bracket')->setCssClass('menu-item-return');
    }

    public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('styles/admin.css');
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
    $user = $this->doctrine->getRepository(User::class)->findOneBy(['email' => $user->getUserIdentifier()]);
        return parent::configureUserMenu($user)
            // use the given $user object to get the username
            ->setName($user->getFirstName() . ' ' . $user->getLastName())
            // you can also pass an email address to use gravatar's service
            ->setGravatarEmail($user->getEmail())

            // you can use any type of menu item, except submenus
            ->addMenuItems([
                MenuItem::section(),
                MenuItem::linkToLogout('Logout', 'fa fa-sign-out'),
            ]);
    }
}
