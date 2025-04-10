<?php

namespace App\Controller\Admin;

use App\Entity\Produit;
use App\Form\ImageType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
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
            CollectionField::new('images', 'Images associées')
                ->renderExpanded() // Affiche les entrées sous forme complète et non dans un champ replié
                ->formatValue(function ($value, Produit $entity) {
                    // Génération des miniatures pour chaque image
                    $html = '<div style="display: flex; flex-wrap: wrap; gap: 10px;">';
                    foreach ($entity->getImages() as $image) {
                        $html .= '<img src="' . $image->getLink() . '" alt="Image" style="width: 100px; height: 100px; object-fit: cover; border-radius: 5px; border: 1px solid #ccc;">';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->onlyOnIndex(),
            CollectionField::new('images', 'Images associées')
                ->setEntryType(ImageType::class) // Définit un formulaire personnalisé pour chaque entrée de collection (voir plus bas)
                ->setFormTypeOptions([
                    'by_reference' => false, // Important pour gérer correctement les relations "ManyToMany"
                ])
                ->renderExpanded() // Ouvre directement les sous-formulaires dans le formulaire principal
                ->allowAdd() // Autorise l'ajout d'images
                ->allowDelete() // Autorise la suppression d'images
                ->onlyOnForms()

        ];
    }
}
