<?php

namespace App\Form;

use App\Entity\Gender;
use App\Entity\State;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookingStep2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isAdmin = $options['is_admin'] ?? false;

        $builder
            ->add('customerLastName', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('customerFirstName', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('customerEmail', EmailType::class, [
                'label' => 'Email'
            ])
            ->add('customerGender', EntityType::class, [
                'class' => Gender::class,
                'required' => true,
                'placeholder' => 'Choisissez un genre',
                'label' => 'Genre'
            ])
            ->add('customerAdress', TextType::class, [
                'label' => 'N° et Rue'
            ])
            ->add('customerZipCode', TextType::class, [
                'label' => 'Code postal'
            ])
            ->add('customerCity', TextType::class, [
                'label' => 'Ville'
            ])
            ->add('customerPhone', TelType::class, [
                'label' => 'Téléphone'
            ])
        ;
        if ($isAdmin) {
            $builder->add('worker', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'fullName',
                'required' => false, // permet la sélection de "Aucun" (null)
                'placeholder' => 'Aucun',
                'label' => 'Employé(e) attribué(e)'
            ]);

            $builder->add('state', EntityType::class, [
                'class' => State::class,
                'choice_label' => 'name',
                'required' => true,
                'placeholder' => 'Statut du rendez-vous',
                'label' => 'Statut'
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'is_admin' => false,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // L'ID ci-dessous doit être utilisé des deux côtés !
            'csrf_token_id'   => 'booking_item',
        ]);
    }
}
