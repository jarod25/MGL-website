<?php

namespace App\Form\User;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'account.form.firstname.label',
                'attr' => [
                    'placeholder' => 'account.form.firstname.placeholder',
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'constraints' => [
                    new NotBlank(message: 'account.validation.firstname_required'),
                ],
                'required' => false,
            ])
            ->add('lastname', TextType::class, [
                'label' => 'account.form.lastname.label',
                'attr' => [
                    'placeholder' => 'account.form.lastname.placeholder',
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'constraints' => [
                    new NotBlank(message: 'account.validation.lastname_required'),
                ],
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'account.form.email.label',
                'attr' => [
                    'placeholder' => 'account.form.email.placeholder',
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'constraints' => [
                    new NotBlank(message: 'account.validation.email_required'),
                    new Email(message: 'account.validation.email_invalid'),
                ],
                'required' => false,
            ]);

        if ($options['has_lan_participant']) {
            $builder->add('discordPseudo', TextType::class, [
                'label' => 'account.form.discord_pseudo.label',
                'mapped' => false,
                'required' => false,
                'data' => $options['discord_pseudo'],
                'attr' => [
                    'placeholder' => 'account.form.discord_pseudo.placeholder',
                ],
                'help' => 'account.form.discord_pseudo.help',
                'row_attr' => [
                    'class' => 'form-floating',
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'discord_pseudo' => null,
            'has_lan_participant' => false,
        ]);

        $resolver->setAllowedTypes('discord_pseudo', ['null', 'string']);
        $resolver->setAllowedTypes('has_lan_participant', 'bool');
    }
}
