<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

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
                    // Prévisuel HTML des images
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
            DateField::new('editedTime', 'Dernière modification'),
        ];
    }
}
