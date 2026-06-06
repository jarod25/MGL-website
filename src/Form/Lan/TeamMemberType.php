<?php

namespace App\Form\Lan;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class TeamMemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('participantEmail', TextType::class, [
                'label' => 'team.member.form.participant_email.label',
                'attr' => [
                    'placeholder' => 'team.member.form.participant_email.placeholder',
                ],
                'constraints' => [new NotBlank(message: 'team.validation.participant_identifier_required')],
            ])
            ->add('inGamePseudo', TextType::class, [
                'label' => 'team.member.form.in_game_pseudo.label',
                'attr' => [
                    'placeholder' => 'team.member.form.in_game_pseudo.placeholder',
                ],
                'constraints' => [new NotBlank(message: 'team.validation.in_game_pseudo_required')],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
