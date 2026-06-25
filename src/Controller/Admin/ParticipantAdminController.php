<?php

namespace App\Controller\Admin;

use App\Entity\Participant;
use App\Entity\Subscription;
use App\Enum\RegistrationStatusEnum;
use App\Repository\ParticipantRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\TeamMemberRepository;
use App\Repository\TeamRepository;
use App\Service\Admin\ParticipantExcelExportService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class ParticipantAdminController extends AbstractController
{
    #[Route('/admin/lan/participants', name: 'app_admin_lan_participant_index', methods: ['GET'])]
    public function index(
        Request $request,
        ParticipantRepository $participantRepository,
        SubscriptionRepository $subscriptionRepository,
        PaginatorInterface $paginator,
    ): Response {
        $search = trim((string) $request->query->get('q', ''));
        $status = $this->resolveStatus($request->query->get('status'));
        $subscription = $this->resolveSubscription($request->query->get('subscription'), $subscriptionRepository);

        $pagination = $paginator->paginate(
            $participantRepository->createAdminListQueryBuilder($search !== '' ? $search : null, $status, $subscription),
            max(1, $request->query->getInt('page', 1)),
            20,
            [
                'pageParameterName' => 'page',
                'defaultSortFieldName' => 'p.createdAt',
                'defaultSortDirection' => 'desc',
            ],
        );

        return $this->render('admin/lan/participant/index.html.twig', [
            'pagination' => $pagination,
            'subscriptions' => $subscriptionRepository->findBy([], ['name' => 'ASC']),
            'statuses' => RegistrationStatusEnum::cases(),
            'filters' => [
                'q' => $search,
                'status' => $status?->value,
                'subscription' => $subscription?->getId(),
            ],
        ]);
    }


    #[Route('/admin/lan/participants/export.xlsx', name: 'app_admin_lan_participant_export', methods: ['GET'])]
    public function export(ParticipantExcelExportService $exportService): StreamedResponse
    {
        $filename = 'mgl-participants-'.(new \DateTimeImmutable())->format('Y-m-d_H-i').'.xlsx';
        $writer = $exportService->createWriter();

        $response = new StreamedResponse(static function () use ($writer): void {
            $writer->save('php://output');
        });
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $filename));
        $response->headers->set('Cache-Control', 'private, no-store, no-cache, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    #[Route('/admin/lan/participants/{id}', name: 'app_admin_lan_participant_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(Participant $participant, TeamMemberRepository $teamMemberRepository, TeamRepository $teamRepository): Response
    {
        return $this->render('admin/lan/participant/show.html.twig', [
            'participant' => $participant,
            'teamMembers' => $teamMemberRepository->findByParticipant($participant),
            'captainTeams' => $teamRepository->findByCaptain($participant),
        ]);
    }

    private function resolveStatus(mixed $status): ?RegistrationStatusEnum
    {
        if (!is_string($status) || $status === '') {
            return null;
        }

        return RegistrationStatusEnum::tryFrom($status);
    }

    private function resolveSubscription(mixed $subscriptionId, SubscriptionRepository $subscriptionRepository): ?Subscription
    {
        if (!is_numeric($subscriptionId) || (int) $subscriptionId <= 0) {
            return null;
        }

        $subscription = $subscriptionRepository->find((int) $subscriptionId);

        return $subscription instanceof Subscription ? $subscription : null;
    }
}
