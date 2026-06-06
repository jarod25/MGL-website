<?php

namespace App\Form\Lan;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ParticipantRegistrationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'registration.form.firstname.label',
                'attr' => [
                    'placeholder' => 'registration.form.firstname.placeholder',
                ],
                'constraints' => [new NotBlank(message: 'registration.validation.firstname_required')],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'registration.form.lastname.label',
                'attr' => [
                    'placeholder' => 'registration.form.lastname.placeholder',
                ],
                'constraints' => [new NotBlank(message: 'registration.validation.lastname_required')],
            ])
            ->add('email', EmailType::class, [
                'label' => 'registration.form.email.label',
                'attr' => [
                    'placeholder' => 'registration.form.email.placeholder',
                ],
                'constraints' => [
                    new NotBlank(message: 'registration.validation.email_required'),
                    new Email(message: 'registration.validation.email_invalid'),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'registration.validation.password_mismatch',
                'first_options' => [
                    'label' => 'registration.form.password.first_label',
                    'attr' => [
                        'placeholder' => 'registration.form.password.first_placeholder',
                    ],
                ],
                'second_options' => [
                    'label' => 'registration.form.password.second_label',
                    'attr' => [
                        'placeholder' => 'registration.form.password.second_placeholder',
                    ],
                ],
                'constraints' => [
                    new NotBlank(message: 'registration.validation.password_required'),
                    new Length(min: 8, minMessage: 'registration.validation.password_min_length'),
                ],
            ])
            ->add('discordPseudo', TextType::class, [
                'label' => 'registration.form.discord_pseudo.label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'registration.form.discord_pseudo.placeholder',
                ],
                'help' => 'registration.form.discord_pseudo.help',
            ])
            ->add('isMajorConfirmed', CheckboxType::class, [
                'label' => 'registration.form.is_major_confirmed.label',
                'constraints' => [new IsTrue(message: 'registration.validation.major_confirmation_required')],
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
