<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;


class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            //FormField::addTab('Informations Principals', propertySuffix: 'main'),
            FormField::addColumn(4,'Information principals'),
            EmailField::new('email')->setLabel('Adresse mail'),
            TextField::new('first_name')->setLabel('Prénom'),
            TextField::new('last_name')->setLabel('Nom'),
            AssociationField::new('gender')->setLabel('Genre'),


            //FormField::addTab('Informations Personnelles', propertySuffix: 'personal'),
            FormField::addColumn(4,'Informations Personnelles'),
            DateField::new('birth_date')->setLabel('Date de naissance')->hideOnIndex(),
            TextField::new('adress')->setLabel('Adresse')->hideOnIndex(),
            NumberField::new('zipcode')->setLabel('Code postal')->hideOnIndex(),
            TextField::new('city')->setLabel('Ville')->hideOnIndex(),
            TextField::new('phone')->setLabel('Numéro de téléphone'),


            //FormField::addTab('Administration du compte', propertySuffix: 'admin'),
            FormField::addColumn(4, 'Administration du compte'),
            ChoiceField::new('roles')
                ->setChoices([
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ])
                ->allowMultipleChoices()
                ->renderAsBadges()
                ->renderAsBadges([
                    'ROLE_USER' => 'primary',
                    'ROLE_ADMIN' => 'danger',
                ])
                ->setLabel('Types de compte')
                ->hideOnIndex(),
            TextField::new('mainRole', 'Rôle')
                ->formatValue(function ($value, User $entity) {
                    $roles = $entity->getRoles();
                    if (in_array('ROLE_ADMIN', $roles)) {
                        return 'ROLE_ADMIN';
                    } elseif (in_array('ROLE_USER', $roles)) {
                        return 'ROLE_USER';
                    }

                    return 'NO_ROLE';
                })
                ->setTemplatePath('admin/field/role_badge.html.twig')
            ->onlyOnIndex(),
            BooleanField::new("want_newsletter")
                ->setLabel('Abonné(e) au newsletter')
            ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Gestion des comptes client') // Titre pour la page d'index
            ->setPageTitle('detail', fn (User $user) => sprintf('Détails de %s', $user->getFirstName() . ' ' . $user->getLastName())) // Titre pour la page de détails
            ->setPageTitle('edit', fn (User $user) => sprintf('Modification de %s', $user->getFirstName() . ' ' . $user->getLastName())) // Titre pour la page d'édition
            ->setPageTitle('new', 'Créer un nouveau compte client'); // Titre pour la page de création
    }
}
