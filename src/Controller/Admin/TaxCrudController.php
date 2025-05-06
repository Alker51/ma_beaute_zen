<?php

namespace App\Controller\Admin;

use App\Entity\Tax;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TaxCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tax::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name')->setLabel("Description"),
            NumberField::new('value')->setLabel("Valeur ( en % )")->HideOnIndex(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Gestion des Taux de TVA') // Titre pour la page d'index
            ->setPageTitle('detail', fn (Tax $tax) => sprintf('Détails de la taxe: %s', $tax->getName())) // Titre pour la page de détails
            ->setPageTitle('edit', fn (Tax $tax) => sprintf('Modifier la taxe: %s', $tax->getName())) // Titre pour la page d'édition
            ->setPageTitle('new', 'Créer un nouveau taux'); // Titre pour la page de création
    }

    public function configureActions(Actions $actions): Actions
    {
        // Supprime le bouton "Modifier"


        // Activer l'action "Détail"
        $detailAction = Action::new(Action::DETAIL)
            ->linkToCrudAction('detail');


        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Nouvelle taxe'); // Remplacer le texte
            })
            ->add(Crud::PAGE_INDEX, $detailAction)
            ;
    }
}
