<?php

namespace App\Form;

use App\Entity\Booking;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isAdmin = $options['is_admin'] ?? false;

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
        if ($isAdmin) {
            $builder->add('worker', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'fullName',
                'required' => false, // permet la sélection de "Aucun" (null)
                'placeholder' => 'Aucun',
            ]);
        }

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
            'is_admin' => false,
        ]);
    }
}
