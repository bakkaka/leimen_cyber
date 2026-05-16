<?php

namespace App\Form;

use App\Entity\Prerequisite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrerequisiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Installer VirtualBox et Kali Linux']
            ])
            ->add('excerpt', TextareaType::class, [
                'label' => 'Extrait (SEO / résumé)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 2]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu (HTML autorisé)',
                'attr' => ['class' => 'form-control', 'rows' => 12]
            ])
            ->add('isPublished', CheckboxType::class, [
                'label' => 'Publié',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Prerequisite::class,
        ]);
    }
}