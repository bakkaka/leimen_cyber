<?php

namespace App\Form;

use App\Entity\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de la formation',
                'attr' => ['class' => 'form-control']
            ])
            ->add('shortDescription', TextareaType::class, [
                'label' => 'Description courte',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 2]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description complète',
                'attr' => ['class' => 'form-control', 'rows' => 6]
            ])
            ->add('level', ChoiceType::class, [
                'label' => 'Niveau',
                'choices' => [
                    'Débutant' => 'beginner',
                    'Intermédiaire' => 'intermediate',
                    'Avancé' => 'advanced',
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('price', IntegerType::class, [
                'label' => 'Prix (en centimes)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Durée (minutes)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('whatYouWillLearn', TextareaType::class, [
                'label' => 'Ce que vous allez apprendre (un point par ligne)',
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'form-control', 'rows' => 4]
            ])
            ->add('requirements', TextareaType::class, [
                'label' => 'Prérequis (un point par ligne)',
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3]
            ])
            ->add('targetAudience', TextareaType::class, [
                'label' => 'Public cible (un point par ligne)',
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3]
            ])
            ->add('hasCertificate', CheckboxType::class, [
                'label' => 'Certificat inclus',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('isPublished', CheckboxType::class, [
                'label' => 'Publier la formation',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}