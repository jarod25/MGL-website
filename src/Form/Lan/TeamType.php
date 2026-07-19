<?php

namespace App\Form\Lan;

use App\Entity\Game;
use App\Repository\GameRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'team.form.name.label',
                'attr' => [
                    'placeholder' => 'team.form.name.placeholder',
                ],
                'constraints' => [new NotBlank(message: 'team.validation.name_required')],
            ])
            ->add('game', EntityType::class, [
                'class' => Game::class,
                'choice_label' => 'name',
                'label' => 'team.form.game.label',
                'placeholder' => 'team.form.game.placeholder',
                'query_builder' => static fn (GameRepository $gameRepository) => $gameRepository->createQueryBuilder('g')
                    ->andWhere('g.isActive = :active')
                    ->setParameter('active', true)
                    ->orderBy('g.day', 'ASC')
                    ->addOrderBy('g.name', 'ASC'),
                'constraints' => [new NotBlank(message: 'team.validation.game_required')],
            ])
            ->add('captainInGamePseudo', TextType::class, [
                'label' => 'team.form.captain_in_game_pseudo.label',
                'attr' => [
                    'placeholder' => 'team.form.captain_in_game_pseudo.placeholder',
                ],
                'constraints' => [new NotBlank(message: 'team.validation.captain_in_game_pseudo_required')],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}