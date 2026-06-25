<?php

namespace App\Service\Admin;

use App\Entity\Participant;
use App\Entity\TeamMember;
use App\Repository\ParticipantRepository;
use App\Repository\TeamMemberRepository;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ParticipantExcelExportService
{
    public function __construct(
        private readonly ParticipantRepository $participantRepository,
        private readonly TeamMemberRepository $teamMemberRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function createWriter(): Xlsx
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Participants');

        $headers = [
            'admin.participant.export.columns.lastname',
            'admin.participant.export.columns.firstname',
            'admin.participant.export.columns.email',
            'admin.participant.export.columns.discord_pseudo',
            'admin.participant.export.columns.major_confirmed',
            'admin.participant.export.columns.status',
            'admin.participant.export.columns.subscription',
            'admin.participant.export.columns.subscription_price',
            'admin.participant.export.columns.duration',
            'admin.participant.export.columns.game',
            'admin.participant.export.columns.day',
            'admin.participant.export.columns.team',
            'admin.participant.export.columns.role',
            'admin.participant.export.columns.in_game_pseudo',
        ];

        foreach ($headers as $index => $header) {
            $sheet->setCellValueExplicit([$index + 1, 1], $this->translator->trans($header), DataType::TYPE_STRING);
        }

        $membersByParticipant = $this->groupMembersByParticipant();
        $row = 2;
        foreach ($this->participantRepository->findAllForAdminExport() as $participant) {
            $members = $membersByParticipant[$participant->getId()] ?? [null];
            foreach ($members as $member) {
                $this->writeParticipantRow($sheet, $row, $participant, $member);
                ++$row;
            }
        }

        $lastRow = max(1, $row - 1);
        $sheet->getStyle('A1:N1')->getFont()->setBold(true);
        $sheet->setAutoFilter('A1:N'.$lastRow);
        $sheet->freezePane('A2');
        $sheet->getStyle('H2:H'.$lastRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);

        foreach (range('A', 'N') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return new Xlsx($spreadsheet);
    }

    /**
     * @return array<int, TeamMember[]>
     */
    private function groupMembersByParticipant(): array
    {
        $membersByParticipant = [];
        foreach ($this->teamMemberRepository->findAllForAdminExport() as $member) {
            $participantId = $member->getParticipant()?->getId();
            if ($participantId !== null) {
                $membersByParticipant[$participantId][] = $member;
            }
        }

        return $membersByParticipant;
    }

    private function writeParticipantRow(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $row, Participant $participant, ?TeamMember $member): void
    {
        $subscription = $participant->getSubscription();
        $team = $member?->getTeam();
        $game = $member?->getGame();
        $isCaptain = $team?->getCaptain()?->getId() === $participant->getId();

        $strings = [
            1 => $participant->getLastname(),
            2 => $participant->getFirstname(),
            3 => $participant->getEmail(),
            4 => $participant->getDiscordPseudo(),
            5 => $this->translator->trans($participant->isMajorConfirmed() ? 'admin.common.yes' : 'admin.common.no'),
            6 => $this->translator->trans('registration.status.'.$participant->getRegistrationStatus()->value),
            7 => $subscription?->getName(),
            9 => $subscription ? $this->translator->trans('admin.subscription.duration_days', ['%days%' => $subscription->getDurationDays()]) : null,
            10 => $game?->getName(),
            11 => $game ? $this->translator->trans('game.day.'.$game->getDay()->value) : null,
            12 => $team?->getName(),
            13 => $member ? $this->translator->trans($isCaptain ? 'team.show.captain' : 'team.show.member') : null,
            14 => $member?->getInGamePseudo(),
        ];

        foreach ($strings as $column => $value) {
            $sheet->setCellValueExplicit([$column, $row], (string) ($value ?? ''), DataType::TYPE_STRING);
        }

        if ($subscription !== null && $subscription->getPrice() !== null) {
            $sheet->setCellValue([8, $row], $subscription->getPrice() / 100);
        }
    }
}
