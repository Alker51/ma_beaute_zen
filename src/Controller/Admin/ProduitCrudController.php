<?php

namespace App\Controller\Admin;

use App\Entity\Produit;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProduitCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Produit::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name')->setLabel('Nom du produit'),
            TextEditorField::new('description')->hideOnIndex(),
            BooleanField::new('active')->setLabel('Produit en ligne ?'),
            NumberField::new('priceHT')->setLabel('Prix HT'),
            AssociationField::new('taxeId')
                ->setLabel('Taxe appliquée')
                ->formatValue(function ($value, Produit $entity) {
                    // Affiche une chaîne lisible
                    return $entity->getTaxeId()
                        ? $entity->getTaxeId()->getName() . ' (' . $entity->getTaxeId()->getValue() . '%)'
                        : 'Aucune taxe';
                }),
            NumberField::new('prixTTC', 'Prix TTC')
                ->setLabel('Prix TTC')
                ->formatValue(function ($value, Produit $entity) {
                    return number_format($entity->getPrixTTC(), 2, ',', ' ') . ' €';
                })
                ->setFormTypeOption('disabled', true),

        ];
    }
}
