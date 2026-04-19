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
                'label' => 'Mot de passe actuel',
                'attr' => [
                    'placeholder' => 'Mot de passe actuel',
                    'autocomplete' => 'current-password'
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir votre mot de passe actuel',
                    ]),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les deux mots de passe doivent être identiques.',
                'first_options' => [
                    'attr' => [
                        'label' => 'Mot de passe',
                        'placeholder' => 'Mot de passe',
                        'autocomplete' => 'new-password'
                    ],
                    'row_attr' => [
                        'class' => 'form-floating',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez saisir votre mot de passe',
                        ]),
                    ],
                ],
                'second_options' => [
                    'attr' => [
                        'label' => 'Confirmation du mot de passe',
                        'placeholder' => 'Confirmation du mot de passe',
                        'autocomplete' => 'new-password'
                    ],
                    'row_attr' => [
                        'class' => 'form-floating',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez confirmer votre mot de passe',
                        ]),
                    ],
                ],
                'attr' => [
                    'placeholder' => 'Mot de passe',
                    'autocomplete' => 'new-password'
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Length(
                        min: 8,
                        max: 4096,
                        minMessage: 'Votre mot de passe doit comporter au moins {{ limit }} caractères',
                        maxMessage: 'Votre mot de passe doit comporter maximum {{ limit }} caractères',
                    ),
                    new Regex([
                        'pattern' => match ($_ENV['PASSWORD_STRENGTH_VALUE']) {
                            "1" => '/^(?=.*[a-z]).{8,}$/',
                            "2" => '/^(?=.*[a-z])(?=.*[A-Z]).{8,}$/',
                            "3" => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}$/',
                            "4" => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*()\-_=+{};:,<.>]).{8,}$/',
                        },
                        'message' => 'Le mot de passe ne respecte pas les critères de sécurité',
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
