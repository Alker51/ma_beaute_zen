<?php

namespace App\Controller\Admin;

use App\Entity\Produit;
use App\Form\ImageType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
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
            FormField::addColumn(4,'Informations du produit'),
            TextField::new('name')->setLabel('Nom du produit'),
            TextEditorField::new('description')->hideOnIndex(),
            BooleanField::new('active')->setLabel('Produit en ligne ?'),
            NumberField::new('delay')->setLabel('Durée de la préstation (en minutes)'),

            FormField::addColumn(4,'Prix, Stock et Promotion'),
            FormField::addFieldset('Prix et TVA'),
            NumberField::new('priceHT')->setLabel('Prix HT'),
            AssociationField::new('taxeId')
                ->setLabel('TVA appliquée')
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

            FormField::addFieldset('Promotion'),
            BooleanField::new('promoActive')->setLabel('Promotion en cours ?'),
            NumberField::new('promoPercent')->setLabel('Pourcentage promotionnel (si promo activé.'),
            FormField::addFieldset('Stock'),
            NumberField::new('stock')->setLabel('Stocks disponibles')
                ->formatValue(function ($value, $entity) {
                    // Prévisuel HTML des images
                    $html = '<button class="btn btn-';

                    if ($entity->getStock() >= 5) {
                        $html .= 'success';
                    } elseif ($entity->getStock() > 0 && $entity->getStock() < 5) {
                        $html .= 'warning';
                    } elseif ($entity->getStock() <= 0) {
                        $html .= 'danger';
                    }

                    $html .= '">' . $entity->getStock() . '</span>';
                    return $html;
                }),

            FormField::addColumn(4,'Visuel du produit'),
            CollectionField::new('images', 'Images associées')
                ->setEntryType(ImageType::class) // Utiliser un sous-formulaire pour chaque image
                ->renderExpanded() // Ouvrir les sous-formulaires dans le formulaire principal
                ->allowAdd() // Permettre d’ajouter des images
                ->allowDelete() // Permettre de supprimer des images
                ->setFormTypeOption('by_reference', false) // Assurer une bonne gestion avec Doctrine
                ->setFormTypeOption('entry_options', [
                    'label' => false, // Pas de label pour chaque image
                ])
                ->formatValue(function ($value, $entity) {
                    // Prévisuel HTML des images
                    $html = '<div style="display: flex; gap: 10px;">';
                    foreach ($entity->getImages() as $image) {
                        $html .= '<img src="' . $image->getLink() . '" alt="Image" style="width: 75px; height: 75px; object-fit: cover; border-radius: 5px; border: 1px solid #ccc;">';
                    }
                    $html .= '</div>';

                    return $html;
                })

        ];
    }
}
