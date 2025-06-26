<?php

namespace App\Controller\Admin;

use App\Controller\StateController;
use App\Entity\Contact;
use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
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
            FormField::addFieldset('Informations de la demande'),
            TextField::new('title', 'Sujet'),
            AssociationField::new('state', 'État de la demande')
                ->formatValue(function ($value, $entity) {
                    $state = $entity->getState();
                    $color = new StateController()->getBootstrapColorByState($state->getId());

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
                            $time = $interval->days == 0 ? 'Aujourd\'hui' : 'il y a ' . $interval->days . ' jour' . ($interval->days > 1 ? 's' : '');


                            $info = ' ( Dernière réponse : ' . $time .' )';
                        }

                        $formatter = new IntlDateFormatter(
                            'fr_FR', // Locale en français
                            IntlDateFormatter::LONG, // Format long, avec le mois en toutes lettres
                            IntlDateFormatter::SHORT
                        );


                        return '<span class="badge badge-'.$color.'">' . mb_strtoupper($formatter->format($entity->getEditedTime())) . $info . '</span>';
                    }
                ),
            FormField::addFieldset('Messages'),
            TextAreaField::new('detail', 'Message initial')->setDisabled(true)->formatValue(function ($value, $entity) {
                return strip_tags($entity->getDetail());
            })
        ];

        if($pageName === Crud::PAGE_DETAIL) {
            $fields[] = CollectionField::new('replies', 'Réponses')
                ->onlyOnDetail()
                ->formatValue(function($value, $entity) {
                    $output = '<ul style="list-style:none;padding-left:0">';
                    $i = 0;
                    $replies = $entity->getReplies();
                    $replies = $replies->toArray();

                    usort($replies, function($a, $b) {
                        return $b->getReplayDate() <=> $a->getReplayDate();
                    });

                    foreach ($replies as $reply) {
                        $i > 0 ? $output .= '<hr>':'';
                        $output .= "<li><strong>" . $reply->getMessage() . "</strong><br><small>De " . $reply->getAuthorString() . '<br>Date : ' . $reply->getReplayDate()->format('d/m/Y H:i') . "</small></li>";
                        $i++;

                    }
                    $output .= '</ul>';
                    return $output;
                })
                ->setDisabled(true);
        } else {
            $fields[] = CollectionField::new('replies', 'Réponses');
        }

        return $fields;
    }

    public function configureActions(Actions $actions): Actions
    {
        // Définition du bouton "Répondre"
        $reply = Action::new('reply', 'Répondre', 'fa fa-reply')
            ->linkToRoute('app_reply', function ($entity) {
                return [
                    'contactId' => $entity->getId(),
                ];
            })
            ->addCssClass('btn btn-primary'); // Style Bootstrap

        $solve = Action::new('solve', 'Résoudre', 'fa fa-check')
            ->linkToRoute('app_contact_solve', function ($entity) {
                return [
                    'id' => $entity->getId(),
                ];
            })
            ->addCssClass('btn btn-success');

        $discontinue = Action::new('discontinue', 'Abandonner', 'fa fa-trash-can')
            ->linkToRoute('app_contact_discontinue', function ($entity) {
                return [
                    'id' => $entity->getId(),
                ];
            })
            ->addCssClass('btn btn-danger');

        // Activer l'action "Détail"
        $detailAction = Action::new(Action::DETAIL)
            ->linkToCrudAction('detail');


        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Nouvelle demande client'); // Remplacer le texte
            })
            ->add(Crud::PAGE_INDEX, $detailAction)
            ->add(Crud::PAGE_DETAIL, $reply)
            ->add(Crud::PAGE_EDIT, $reply)
            ->add(Crud::PAGE_DETAIL, $solve)
            ->add(Crud::PAGE_EDIT, $solve)
            ->add(Crud::PAGE_DETAIL, $discontinue)
            ->add(Crud::PAGE_EDIT, $discontinue)
            ->disable(Crud::PAGE_EDIT)
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
