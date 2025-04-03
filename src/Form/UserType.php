<?php

namespace App\Form;

use App\Entity\Gender;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse mail',
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ]
                ],
                'second_options' => [
                    'label' => 'Vérifier le mot de passe',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ]
                ]
            ])
            ->add('first_name', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('last_name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('birth_date', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'choice',
            ])
            ->add('adress', TextType::class, [
                'label' => 'Adresse',
            ])
            ->add('zipcode', NumberType::class, [
                'label' => 'Code postal',
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
            ])
            ->add('gender', EntityType::class, [
                'class' => Gender::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisissez un genre',
            ])
            ->add('phone', NumberType::class, [
                'label' => 'Numéro de téléphone',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'user_csrf_token',
        ]);
    }
}
