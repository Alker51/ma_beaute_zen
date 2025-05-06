<?php

namespace App\Controller\Admin;

use App\Entity\Produit;
use App\Form\ImageType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
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
                ->setLabel('Produit en ligne')
                ->renderAsBadges([
                    1 => 'success', // Si la valeur est 1
                    0 => 'danger',  // Si la valeur est 0
                ])
                ->setChoices([
                    'Oui' => 1,
                    'Non' => 0,
                ]); // Pour s'assurer que TINYINT est mappé correctement
        } else {
            $fields[] = BooleanField::new('active', 'Produit en ligne')
                ->renderAsSwitch(false);
        }

        $fieldsAlt = [
            NumberField::new('delay', 'Durée de la préstation')
                ->formatValue(function ($value, Produit $entity) {
                    return $entity->getDelay() . ' minutes.';
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
                    1 => 'success', // Si la valeur est 1
                    0 => 'danger',  // Si la valeur est 0
                ])
                ->setChoices([
                    'Oui' => 1,
                    'Non' => 0,
                ]); // Pour s'assurer que TINYINT est mappé correctement
        } else {
            $fields[] = BooleanField::new('promoActive', 'Promotion en cours')
                ->renderAsSwitch(false);
        }

        if (!is_null($product) && $product->isPromoActive() === 1) {
            $fields[] = NumberField::new('promoPercent', '% de réduction')
                ->formatValue(function ($value, Produit $entity) {
                    return '<span class="badge badge-danger">'. number_format($entity->getPromoPercent(), 2, ',', ' ') . ' % de réduction.</span>';
                });
        } else {
            $fields[] = NumberField::new('promoPercent', '% de réduction')
                ->formatValue(function ($value, Produit $entity) {
                    if(!$entity->isPromoActive())
                        return '<span class="badge badge-info"> Aucune réduction</span>';

                    return '<span class="badge badge-danger">'. number_format($entity->getPromoPercent(), 2, ',', ' ') . ' % de réduction.</span>';
                });
        }

        $fields[] = FormField::addFieldset('Stock');

        if (in_array($pageName, [Crud::PAGE_INDEX, Crud::PAGE_DETAIL])) {
            $fields[] = ChoiceField::new('noStockProduct')
                ->setLabel('Produit sans stock')
                ->renderAsBadges([
                    1 => 'success', // Si la valeur est 1
                    0 => 'danger',  // Si la valeur est 0
                ])
                ->setChoices([
                    'Oui' => 1,
                    'Non' => 0,
                ]); // Pour s'assurer que TINYINT est mappé correctement
        } else {
            $fields[] = BooleanField::new('noStockProduct', 'Produit sans stock')
                ->renderAsSwitch(false);
        }

        //TODO : Trouver pourquoi $product est null alors que les autres test fonctionne.
        //WARNING : Ne pas faire de var_dump de product sinon tout freeze.

        if(!is_null($product) && !$product->isNoStockProduct() && $pageName !== Crud::PAGE_DETAIL){
            $fields[] = NumberField::new('stock', 'Stocks disponibles')
                ->setValue(0)
                ->formatValue(function ($value, $entity) {
                    // Prévisuel HTML des images
                    $html = '<span class="badge badge-';

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
        } else {
            $fields[] = NumberField::new('stock', 'Stocks disponibles')
                ->setValue(0)
                ->formatValue(function ($value, $entity) {
                    // Prévisuel HTML des images
                    $html = '<span class="badge badge-';

                    if($entity->isNoStockProduct())
                        return '<span class="badge badge-secondary">Produit sans stock</span>';

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
        }

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

        $fields = array_merge($fields, $fieldsAlt);

        return $fields;
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
