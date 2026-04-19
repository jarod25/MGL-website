<?php

namespace App\Form\Event;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'row_attr' => [
                    "data-controller" => "ckeditor",
                ],
                'attr' => [
                    "data-ckeditor-target" => "txt",
                ],
                'required' => false,
                'label' => 'Description',
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Date de début',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'Date de fin',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('nbMaxParticipants', IntegerType::class, [
                'label' => 'Nombre de participants',
                'required' => false,
            ])
            ->add('isPublic', ChoiceType::class, [
                'required' => true,
                'label' => 'Évènement public ?',
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => true,
            ])
            ->add('isPayable', ChoiceType::class, [
                'required' => true,
                'label' => 'Évènement payant ?',
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => true,
            ])
            ->add('price', IntegerType::class, [
                'label' => 'Prix',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
