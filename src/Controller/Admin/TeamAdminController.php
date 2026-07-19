<?php

namespace App\Controller\Admin;

use App\Entity\Game;
use App\Entity\Participant;
use App\Entity\Team;
use App\Entity\TeamMember;
use App\Enum\GameDayEnum;
use App\Exception\Lan\LanRegistrationException;
use App\Form\Admin\TeamAdminType;
use App\Form\Admin\TeamMemberAdminType;
use App\Form\Lan\TeamMemberType;
use App\Repository\GameRepository;
use App\Repository\ParticipantRepository;
use App\Repository\TeamMemberRepository;
use App\Repository\TeamRepository;
use App\Service\Lan\TeamManagementService;
use App\Service\Lan\TeamRegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('ROLE_ADMIN')]
final class TeamAdminController extends AbstractController
{
    #[Route('/admin/lan/teams', name: 'app_admin_lan_team_index', methods: ['GET'])]
    public function index(Request $request, TeamRepository $teamRepository, TeamMemberRepository $teamMemberRepository, GameRepository $gameRepository, PaginatorInterface $paginator): Response
    {
        $search = trim((string) $request->query->get('q', ''));
        $game = $this->resolveGame($request->query->get('game'), $gameRepository);
        $day = $this->resolveDay($request->query->get('day'));
        $pagination = $paginator->paginate($teamRepository->createAdminListQueryBuilder($search !== '' ? $search : null, $game, $day), max(1, $request->query->getInt('page', 1)), 20);

        return $this->render('admin/lan/team/index.html.twig', [
            'pagination' => $pagination,
            'games' => $gameRepository->findBy([], ['day' => 'ASC', 'name' => 'ASC']),
            'days' => GameDayEnum::cases(),
            'memberCounts' => $teamMemberRepository->countGroupedByTeams((array) $pagination->getItems()),
            'filters' => ['q' => $search, 'game' => $game?->getId(), 'day' => $day?->value],
        ]);
    }

    #[Route('/admin/lan/teams/{id}', name: 'app_admin_lan_team_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(Team $team, TeamMemberRepository $teamMemberRepository): Response
    {
        $members = $teamMemberRepository->findByTeam($team);

        return $this->render('admin/lan/team/show.html.twig', [
            'team' => $team,
            'members' => $members,
            'memberCount' => count($members),
            'maxPlayers' => $team->getGame()?->getMaxPlayersPerTeam(),
        ]);
    }

    #[Route('/admin/lan/teams/{id}/edit', name: 'app_admin_lan_team_edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function edit(Team $team, Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(TeamAdminType::class, $team);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', $translator->trans('admin.team.flash.updated'));

            return $this->redirectToRoute('app_admin_lan_team_show', ['id' => $team->getId()]);
        }

        return $this->render('admin/lan/team/edit.html.twig', ['team' => $team, 'form' => $form->createView()]);
    }

    #[Route('/admin/lan/teams/{id}/members/new', name: 'app_admin_lan_team_member_new', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function newMember(Team $team, Request $request, ParticipantRepository $participantRepository, TeamMemberRepository $teamMemberRepository, TeamRegistrationService $teamRegistrationService, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(TeamMemberType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $participant = $participantRepository->findOneByEmailOrDiscordPseudo((string) $data['participantEmail']);
            if (!$participant instanceof Participant) {
                $form->get('participantEmail')->addError(new FormError($translator->trans('team.error.member_not_found')));
            } else {
                try {
                    $teamRegistrationService->addMember($team, $participant, (string) $data['inGamePseudo']);
                    $this->addFlash('success', $translator->trans('admin.team.flash.member_added'));
                    return $this->redirectToRoute('app_admin_lan_team_show', ['id' => $team->getId()]);
                } catch (LanRegistrationException $exception) {
                    $this->addFlash('danger', $translator->trans($exception->getMessage()));
                } catch (\Throwable) {
                    $this->addFlash('danger', $translator->trans('team.error.member_add_failed'));
                }
            }
        }

        return $this->render('admin/lan/team/member_new.html.twig', ['team' => $team, 'members' => $teamMemberRepository->findByTeam($team), 'form' => $form->createView()]);
    }

    #[Route('/admin/lan/teams/{teamId}/members/{memberId}/edit', name: 'app_admin_lan_team_member_edit', requirements: ['teamId' => '\\d+', 'memberId' => '\\d+'], methods: ['GET', 'POST'])]
    public function editMember(int $teamId, int $memberId, Request $request, TeamRepository $teamRepository, TeamMemberRepository $teamMemberRepository, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $team = $teamRepository->find($teamId);
        $member = $teamMemberRepository->find($memberId);
        if (!$team instanceof Team || !$member instanceof TeamMember || $member->getTeam()?->getId() !== $team->getId()) {
            throw $this->createNotFoundException($translator->trans('team.error.member_not_found'));
        }

        $form = $this->createForm(TeamMemberAdminType::class, $member);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', $translator->trans('admin.team.flash.member_updated'));
            return $this->redirectToRoute('app_admin_lan_team_show', ['id' => $team->getId()]);
        }

        return $this->render('admin/lan/team/member_edit.html.twig', ['team' => $team, 'member' => $member, 'form' => $form->createView()]);
    }

    #[Route('/admin/lan/teams/{teamId}/members/{memberId}/delete', name: 'app_admin_lan_team_member_delete', requirements: ['teamId' => '\\d+', 'memberId' => '\\d+'], methods: ['POST'])]
    public function deleteMember(int $teamId, int $memberId, Request $request, TeamRepository $teamRepository, TeamMemberRepository $teamMemberRepository, TeamManagementService $teamManagementService, TranslatorInterface $translator): Response
    {
        $team = $teamRepository->find($teamId);
        $member = $teamMemberRepository->find($memberId);
        if (!$team instanceof Team || !$member instanceof TeamMember) {
            throw $this->createNotFoundException($translator->trans('team.error.member_not_found'));
        }
        if (!$this->isCsrfTokenValid('admin_delete_team_member_'.$member->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }
        try {
            $teamManagementService->removeMember($team, $member);
            $this->addFlash('success', $translator->trans('admin.team.flash.member_removed'));
        } catch (\Throwable $throwable) {
            $this->addFlash('danger', $translator->trans($throwable->getMessage() ?: 'team.error.member_remove_failed'));
        }

        return $this->redirectToRoute('app_admin_lan_team_show', ['id' => $team->getId()]);
    }

    #[Route('/admin/lan/teams/{id}/delete', name: 'app_admin_lan_team_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(Team $team, Request $request, TeamManagementService $teamManagementService, TranslatorInterface $translator): Response
    {
        if (!$this->isCsrfTokenValid('admin_delete_team_'.$team->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }
        try {
            $teamManagementService->deleteTeam($team);
            $this->addFlash('success', $translator->trans('admin.team.flash.deleted'));
            return $this->redirectToRoute('app_admin_lan_team_index');
        } catch (\Throwable) {
            $this->addFlash('danger', $translator->trans('team.error.delete_failed'));
            return $this->redirectToRoute('app_admin_lan_team_show', ['id' => $team->getId()]);
        }
    }

    private function resolveGame(mixed $gameId, GameRepository $gameRepository): ?Game
    {
        if (!is_numeric($gameId) || (int) $gameId <= 0) {
            return null;
        }
        $game = $gameRepository->find((int) $gameId);
        return $game instanceof Game ? $game : null;
    }

    private function resolveDay(mixed $day): ?GameDayEnum
    {
        return is_string($day) && $day !== '' ? GameDayEnum::tryFrom($day) : null;
    }
}
