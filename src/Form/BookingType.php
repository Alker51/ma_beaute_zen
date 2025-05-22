<?php

namespace App\Form;

use App\Entity\Booking;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('start', null, [
                'widget' => 'single_text',
                'html5' => true,
                'with_seconds' => false, // facultatif : true pour activer les secondes
                'input' => 'datetime',   // important pour bien accepter la date et l'heure
                'label' => 'Début',

            ])
            ->add('end', null, [
                'widget' => 'single_text',
                'html5' => true,
                'with_seconds' => false,
                'input' => 'datetime',
                'label' => 'Fin',

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
        ]);
    }
}
