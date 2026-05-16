<?php

namespace App\Form;

use App\Entity\Question;
use App\Entity\Quiz;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quiz', EntityType::class, [
                'class' => Quiz::class,
                'choice_label' => 'title',
                'attr' => ['class' => 'form-select']
            ])
            ->add('text', TextareaType::class, [
                'label' => 'Question',
                'attr' => ['class' => 'form-control', 'rows' => 3]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de question',
                'choices' => [
                    'Choix multiples' => 'multiple_choice',
                    'Vrai / Faux' => 'true_false',
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('options', TextareaType::class, [
                'label' => 'Options (une par ligne)',
                'required' => false,
                'help' => 'Pour les questions à choix multiples. Exemple : Option A\nOption B\nOption C',
                'attr' => ['class' => 'form-control', 'rows' => 4]
            ])
            ->add('correctAnswer', TextType::class, [
                'label' => 'Bonne réponse',
                'help' => 'Pour les choix multiples : copiez exactement l’option (ou l’index 0,1,2). Pour Vrai/Faux : "Vrai" ou "Faux".',
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}