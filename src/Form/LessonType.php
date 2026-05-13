<?php

namespace App\Form;

use App\Entity\Lesson;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LessonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de la leçon',
                'attr' => ['class' => 'form-control']
            ])
            ->add('teaser', TextareaType::class, [
                'label' => 'Texte d\'accroche (avant vidéo)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 2]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu texte (après vidéo)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 6]
            ])
            ->add('videoUrl', TextType::class, [
                'label' => 'URL de la vidéo (YouTube)',
                'attr' => ['class' => 'form-control', 'placeholder' => 'https://www.youtube.com/embed/...']
            ])
            ->add('videoPlatform', ChoiceType::class, [
                'label' => 'Plateforme vidéo',
                'choices' => [
                    'YouTube' => 'youtube',
                    'Vimeo' => 'vimeo',
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Durée (minutes)',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('orderNumber', IntegerType::class, [
                'label' => "Ordre d'affichage",
                'attr' => ['class' => 'form-control']
            ])
            ->add('isFreePreview', CheckboxType::class, [
                'label' => 'Leçon gratuite (aperçu)',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('keyTakeaways', TextareaType::class, [
                'label' => 'Points clés (un point par ligne)',
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'form-control', 'rows' => 4]
            ])
            ->add('actionItem', TextareaType::class, [
                'label' => 'Action à réaliser',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 2]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
        ]);
    }
}