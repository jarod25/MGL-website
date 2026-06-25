<?php

namespace App\Form\Admin;

use App\Entity\Subscription;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

final class SubscriptionAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'admin.subscription.name',
            ])
            ->add('helloAssoTierId', TextType::class, [
                'label' => 'admin.subscription.helloasso_tier_id',
                'required' => false,
                'trim' => true,
                'empty_data' => null,
                'constraints' => [new Length(max: 255)],
            ])
            ->add('price', MoneyType::class, [
                'label' => 'admin.subscription.price',
                'currency' => 'EUR',
                'divisor' => 100,
                'scale' => 2,
                'constraints' => [new PositiveOrZero()],
            ])
            ->add('durationDays', ChoiceType::class, [
                'label' => 'admin.subscription.duration',
                'choices' => [
                    'admin.subscription.duration_one_day' => 1,
                    'admin.subscription.duration_two_days' => 2,
                ],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'admin.subscription.active',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Subscription::class,
            'translation_domain' => 'messages',
        ]);
    }
}
