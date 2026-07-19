<?php

namespace App\Form\User;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class SignInType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'signup.form.firstname.label',
                'attr' => [
                    'placeholder' => 'signup.form.firstname.placeholder',
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'constraints' => [
                    new NotBlank(message: 'signup.validation.firstname_required'),
                ],
                'required' => false,
            ])
            ->add('lastname', TextType::class, [
                'label' => 'signup.form.lastname.label',
                'attr' => [
                    'placeholder' => 'signup.form.lastname.placeholder',
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'constraints' => [
                    new NotBlank(message: 'signup.validation.lastname_required'),
                ],
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'signup.form.email.label',
                'attr' => [
                    'placeholder' => 'signup.form.email.placeholder',
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'constraints' => [
                    new NotBlank(message: 'signup.validation.email_required'),
                    new Email(message: 'signup.validation.email_invalid'),
                ],
                'required' => false,
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'signup.validation.password_mismatch',
                'first_options' => [
                    'label' => 'signup.form.password.label',
                    'attr' => [
                        'placeholder' => 'signup.form.password.placeholder',
                        'autocomplete' => 'new-password',
                    ],
                    'row_attr' => [
                        'class' => 'form-floating',
                    ],
                    'constraints' => [
                        new NotBlank(message: 'signup.validation.password_required'),
                    ],
                ],
                'second_options' => [
                    'label' => 'signup.form.password_confirm.label',
                    'attr' => [
                        'placeholder' => 'signup.form.password_confirm.placeholder',
                        'autocomplete' => 'new-password',
                    ],
                    'row_attr' => [
                        'class' => 'form-floating',
                    ],
                    'constraints' => [
                        new NotBlank(message: 'signup.validation.password_confirm_required'),
                    ],
                ],
                'attr' => [
                    'placeholder' => 'signup.form.password.placeholder',
                    'autocomplete' => 'new-password',
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
                        minMessage: 'signup.validation.password_min_length',
                        maxMessage: 'signup.validation.password_max_length',
                    ),
                    new Regex([
                        'pattern' => match ($_ENV['PASSWORD_STRENGTH_VALUE']) {
                            '1' => '/^(?=.*[a-z]).{8,}$/',
                            '2' => '/^(?=.*[a-z])(?=.*[A-Z]).{8,}$/',
                            '3' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}$/',
                            '4' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*()\-_=+{};:,<.>]).{8,}$/',
                        },
                        'message' => 'signup.validation.password_strength_invalid',
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
