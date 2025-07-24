<?php

namespace App\Form;

use App\Entity\Booking;
use App\Entity\Produit;
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
            ->add('customerName', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('customerEmail', EmailType::class, [
                'label' => 'Email'
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
                'required' => true, // permet la sélection de "Aucun" (null)
                'placeholder' => 'Aucun',
                'label' => 'Statut'
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
            'is_admin' => false,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // L'ID ci-dessous doit être utilisé des deux côtés !
            'csrf_token_id'   => 'booking_item',
        ]);
    }
}
