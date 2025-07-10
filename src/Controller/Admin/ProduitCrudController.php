<?php

namespace App\Controller\Admin;

use App\Entity\Produit;
use App\Form\ImageType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
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
        $product = $this->getContext()->getEntity()->getInstance();

        $fields = [
            FormField::addColumn(4,'Informations du produit'),
            FormField::addFieldset(''),
            TextField::new('name', 'Nom'),
            TextEditorField::new('Description')
                ->hideOnIndex()
        ];

        if (in_array($pageName, [Crud::PAGE_INDEX, Crud::PAGE_DETAIL])) {
            $fields[] = ChoiceField::new('active')
                ->setLabel('État du produit')
                ->renderAsBadges([
                    1 => 'success',
                    0 => 'danger',
                ])
                ->setChoices([
                    'En ligne' => 1,
                    'Hors ligne' => 0,
                ]);
        } else {
            $fields[] = BooleanField::new('active', 'Produit en ligne')
                ->renderAsSwitch(false);
        }

        $fieldsAlt = [
            NumberField::new('delay', 'Durée de la préstation (minutes)')
                ->formatValue(function ($value, Produit $entity) {
                    if(is_null($entity->getDelay()) || $entity->getDelay() == 0)
                        return 'Aucune durée indiquée.';

                    $delay = $entity->getDelay();
                    if ($delay >= 60) {
                        $hours = floor($delay / 60);
                        $minutes = $delay % 60;
                        $result = $hours . ' heure' . ($hours > 1 ? 's' : '');

                        if ($minutes > 0) {
                            $result .= ' ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
                        }

                        return $result . '.';
                    }
                    return $delay . ' minute' . ($delay > 1 ? 's' : '') . '.';
                }),

            FormField::addColumn(4,'Prix, Stock et Promotion'),
            FormField::addFieldset('Prix et TVA'),
            NumberField::new('priceHT', 'Prix HT')
                ->formatValue(function ($value, Produit $entity) {
                    return number_format($entity->getPriceHT(), 2, ',', ' ') . ' € HT';
                }),
            AssociationField::new('taxeId', 'TVA appliquée')
                ->formatValue(function ($value, Produit $entity) {
                    // Affiche une chaîne lisible
                    return $entity->getTaxeId() ? $entity->getTaxeId()->getName() . ' (' . $entity->getTaxeId()->getValue() . '%)'
                        : 'Aucune taxe';
                }),
            NumberField::new('prixTTC', 'Prix TTC')
                ->formatValue(function ($value, Produit $entity) {
                    return number_format($entity->getPrixTTC(), 2, ',', ' ') . ' € TTC';
                })
                ->setFormTypeOption('disabled', true),

            FormField::addFieldset('Promotion'),
        ];

        $fields = array_merge($fields, $fieldsAlt);

        if (in_array($pageName, [Crud::PAGE_INDEX, Crud::PAGE_DETAIL])) {
            $fields[] = ChoiceField::new('promoActive')
                ->setLabel('Promotion en cours')
                ->renderAsBadges([
                    1 => 'success',
                    0 => 'danger',
                ])
                ->setChoices([
                    'Oui' => 1,
                    'Non' => 0,
                ])
                ->hideOnIndex();
        } else {
            $fields[] = BooleanField::new('promoActive', 'Promotion en cours')
                ->renderAsSwitch(false)
                ->hideOnIndex();;
        }

        $fields[] = NumberField::new('promoPercent', '% de réduction')
            ->formatValue(function ($value, Produit $entity) {
                if(!$entity->isPromoActive())
                    return '<span class="badge badge-info"> Aucune réduction en cours.</span>';

                return '<span class="badge badge-danger">'. number_format($entity->getPromoPercent(), 2, ',', ' ') . ' % de réduction.</span>';
            });

        $fields[] = FormField::addFieldset('Stock');

        if (in_array($pageName, [Crud::PAGE_INDEX, Crud::PAGE_DETAIL])) {
            $fields[] = ChoiceField::new('noStockProduct')
                ->setLabel('Produit sans stock')
                ->renderAsBadges([
                    1 => 'success',
                    0 => 'danger',
                ])
                ->setChoices([
                    'Oui' => 1,
                    'Non' => 0,
                ])
                ->hideOnIndex();
        } else {
            $fields[] = BooleanField::new('noStockProduct', 'Produit sans stock')
                ->renderAsSwitch(false)
                ->hideOnIndex();
        }

        $fields[] = NumberField::new('stock', 'Stocks disponibles')
            ->formatValue(function ($value, $entity) {
                // Prévisuel HTML des images
                $html = '<span class="badge badge-';

                if($entity->isNoStockProduct())
                    return '<span class="badge badge-secondary">Produit sans stock.</span>';

                if ($entity->getStock() >= 5) {
                    $html .= 'success';
                    $text = '  produit(s) en stock';
                } elseif ($entity->getStock() > 0 && $entity->getStock() < 5) {
                    $html .= 'warning';

                    if($entity->getStock() == 1)
                        $text = '  produit en stock';
                    else
                        $text = '  produit(s) en stock';

                } elseif ($entity->getStock() <= 0) {
                    $html .= 'danger';
                    $text = '  produit en stock';
                }

                $html .= '">' . $entity->getStock() . $text .' </span>';
                return $html;
            });

        $fieldsAlt = [
            FormField::addColumn(4,'Visuel du produit'),
            FormField::addFieldset(''),
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

        return array_merge($fields, $fieldsAlt);
    }

    public function configureActions(Actions $actions): Actions
    {
        // Supprime le bouton "Modifier"


        // Activer l'action "Détail"
        $detailAction = Action::new(Action::DETAIL)
            ->linkToCrudAction('detail');


        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Nouveau produit'); // Remplacer le texte
            })
            ->add(Crud::PAGE_INDEX, $detailAction)
            ;
    }
}
