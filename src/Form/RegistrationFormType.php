<?php
// src/Form/RegistrationFormType.php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
   public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('email', EmailType::class, [
            'label' => 'Email',
            'attr' => [
                'class' => 'form-control',
                'placeholder' => 'votre@email.com'
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez saisir votre adresse email.'
                ])
            ]
        ])

        ->add('fullName', TextType::class, [
            'label' => 'Nom complet',
            'required' => true,
            'attr' => [
                'class' => 'form-control',
                'placeholder' => 'Votre nom et prénom'
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez saisir votre nom complet.'
                ]),
                new Length([
                    'min' => 5,
                    'minMessage' => 'Le nom complet doit contenir au moins {{ limit }} caractères.'
                ])
            ]
        ])

        ->add('phone', TelType::class, [
            'label' => 'Téléphone',
            'required' => true,
            'attr' => [
                'class' => 'form-control',
                'placeholder' => '+212 6XX XXX XXX'
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez saisir votre numéro de téléphone.'
                ])
            ]
        ])

        ->add('age', IntegerType::class, [
            'label' => 'Âge',
            'required' => true,
            'attr' => [
                'class' => 'form-control',
                'placeholder' => 'Ex: 25'
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez saisir votre âge.'
                ])
            ]
        ])

        ->add('city', TextType::class, [
            'label' => 'Ville',
            'required' => true,
            'attr' => [
                'class' => 'form-control',
                'placeholder' => 'Ex: Casablanca'
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez saisir votre ville.'
                ])
            ]
        ])

        ->add('agreeTerms', CheckboxType::class, [
            'label' => 'J\'accepte les conditions d\'utilisation',
            'mapped' => false,
            'attr' => [
                'class' => 'form-check-input'
            ],
            'constraints' => [
                new IsTrue([
                    'message' => 'Vous devez accepter les conditions d\'utilisation.'
                ])
            ]
        ])

        ->add('plainPassword', PasswordType::class, [
            'label' => 'Mot de passe',
            'mapped' => false,
            'attr' => [
                'autocomplete' => 'new-password',
                'class' => 'form-control'
            ],
            'constraints' => [
    new NotBlank(
        message: 'Veuillez saisir un mot de passe.'
    ),
    new Length(
        min: 8,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
        max: 4096
    )
]
        ])

        ->add('confirmPassword', PasswordType::class, [
            'label' => 'Confirmation du mot de passe',
            'mapped' => false,
            'attr' => [
                'autocomplete' => 'new-password',
                'class' => 'form-control'
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez confirmer votre mot de passe.'
                ])
            ]
        ]);
}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_csrf_token',
            'csrf_token_id' => 'register',
        ]);
    }
}