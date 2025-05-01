<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;


class ContactCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Contact::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title', 'Sujet'),
            AssociationField::new('state', 'État de la demande')
                ->formatValue(function ($value, $entity) {
                    $state = $entity->getState();

                    if($state->getId() === 1){
                        $color = "info";
                    }elseif ($state->getId() === 2){
                        $color = "success";
                    }else{
                        $color = "danger";
                    }


                    return '<span class="badge badge-'.$color.'">' . $state->getName() . '</span>';
                }),
            DateField::new('editedTime', 'Dernière modification')
                ->setFormat('dd/MM/yyyy HH:mm'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        // Supprime le bouton "Modifier"
        return $actions->disable(Action::EDIT);
    }
}
