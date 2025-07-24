<?php

namespace App\Form;

use App\Entity\Booking;
use App\Entity\Produit;
use App\Entity\State;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookingStep1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isAdmin = $options['is_admin'] ?? false;

        $builder
            ->add('start', null, [
                'widget' => 'single_text',
                'html5' => true,
                'with_seconds' => false, // facultatif : true pour activer les secondes
                'input' => 'datetime',   // important pour bien accepter la date et l'heure
                'label' => 'Début',

            ])
            ->add('products', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Aucun',
                'multiple' => true,
                'expanded' => true,
            ])
        ;
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
