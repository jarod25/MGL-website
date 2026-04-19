<?php

namespace App\Form\Event;

use App\Model\EventSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => false,
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début',
                'required' => false,
            ]);
        if ($options['user']) {
            $builder->add('isPublic', ChoiceType::class, [
                'label' => 'Évènement public',
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'multiple' => false,
                'required' => false,
            ]);
        }

        $builder->add('isFull', ChoiceType::class, [
            'label' => 'Places disponibles ?',
            'choices' => [
                'Oui' => true,
                'Non' => false,
            ],
            'multiple' => false,
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventSearch::class,
            'method' => 'GET',
            'csrf_protection' => false,
            'user' => null
        ]);
    }
}
