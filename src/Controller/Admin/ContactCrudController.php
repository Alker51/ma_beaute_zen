<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use IntlDateFormatter;


class ContactCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Contact::class;
    }

    public function configureFields(string $pageName): iterable
    {
        setlocale(LC_TIME, 'fr_FR.UTF-8', 'fra');

        $fields = [
            FormField::addColumn(4,'Informations de la demande'),
            FormField::addFieldset(''),
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
                ->setFormat('dd/MM/yyyy HH:mm')
                ->hideOnForm()
                ->formatValue(
                    function ($value, $entity) {
                        $now = new DateTime('now', new \DateTimeZone('Europe/Paris'));

                        $interval = $now->diff($entity->getEditedTime());

                        if($entity->getState()->getId() === 1) {
                            if ($interval->days <= 2) {
                                $color = "success";
                            } elseif ($interval->days >= 4) {
                                $color = "danger";
                            } else {
                                $color = "warning";
                            }
                        } else {
                            $color = "info";
                        }

                        $info = '';

                        if($entity->getState()->getId() === 1){
                            $info = ' (Ouvert depuis ' . $interval->days . ' jour' . ($interval->days > 1 ? 's' : '') . ')';
                        }

                        $formatter = new IntlDateFormatter(
                            'fr_FR', // Locale en français
                            IntlDateFormatter::LONG, // Format long, avec le mois en toutes lettres
                            IntlDateFormatter::SHORT
                        );


                        return '<span class="badge badge-'.$color.'">' . mb_strtoupper($formatter->format($entity->getEditedTime())) . $info . '</span>';
                    }
                ),
            FormField::addColumn(4,'Messages'),
            FormField::addFieldset(''),
            TextAreaField::new('detail', 'Message initial')->setDisabled(true)->formatValue(function ($value, $entity) {
                return strip_tags($entity->getDetail());
            }),
        ];

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
                return $action->setLabel('Nouvelle demande client'); // Remplacer le texte
            })
            ->add(Crud::PAGE_INDEX, $detailAction)
            ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Demande client') // Nouveau texte pour "Créer Utilisateur"
            ->setEntityLabelInPlural('Demandes client')
            ->setDefaultSort(['editedTime' => 'DESC']); // Tri décroissant par date (la plus récente en premier)
    }

}
