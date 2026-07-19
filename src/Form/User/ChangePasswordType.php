<?php

namespace App\Form\User;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ChangePasswordType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('oldPassword', PasswordType::class, [
                'label' => 'account.password.current.label',
                'attr' => [
                    'placeholder' => 'account.password.current.placeholder',
                    'autocomplete' => 'current-password',
                ],
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank(message: 'account.password.required'),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'account.password.mismatch',
                'first_options' => [
                    'label' => 'account.password.new.label',
                    'attr' => [
                        'placeholder' => 'account.password.new.placeholder',
                        'autocomplete' => 'new-password',
                    ],
                    'constraints' => [
                        new NotBlank(message: 'account.password.required'),
                    ],
                ],
                'second_options' => [
                    'label' => 'account.password.confirm.label',
                    'attr' => [
                        'placeholder' => 'account.password.confirm.placeholder',
                        'autocomplete' => 'new-password',
                    ],
                    'constraints' => [
                        new NotBlank(message: 'account.password.confirm_required'),
                    ],
                ],
                'attr' => [
                    'placeholder' => 'account.password.new.placeholder',
                    'autocomplete' => 'new-password',
                ],
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Length(
                        min: 8,
                        max: 4096,
                        minMessage: 'account.password.min_length',
                        maxMessage: 'account.password.max_length',
                    ),
                    new Regex([
                        'pattern' => match ($_ENV['PASSWORD_STRENGTH_VALUE']) {
                            '1' => '/^(?=.*[a-z]).{8,}$/',
                            '2' => '/^(?=.*[a-z])(?=.*[A-Z]).{8,}$/',
                            '3' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}$/',
                            '4' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*()\-_=+{};:,<.>]).{8,}$/',
                        },
                        'message' => 'account.password.strength_invalid',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
