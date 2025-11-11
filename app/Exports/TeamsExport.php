<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\CaseApplication;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeamsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected int $partnerId;

    public function __construct(int $partnerId)
    {
        $this->partnerId = $partnerId;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return CaseApplication::whereHas('case', function ($query) {
            $query->where('partner_id', $this->partnerId);
        })
            ->where('status', 'accepted')
            ->with(['leader', 'case', 'teamMembers.user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Кейс',
            'Лидер команды',
            'Email лидера',
            'Размер команды',
            'Участники команды',
            'Дата создания',
        ];
    }

    /**
     * @param  CaseApplication  $application
     * @return array
     */
    public function map($application): array
    {
        $teamMembers = $application->teamMembers
            ->map(fn ($member) => $member->user->name.' ('.$member->user->email.')')
            ->implode(', ');

        $teamSize = $application->teamMembers->count() + 1; // +1 для лидера

        return [
            $application->id,
            $application->case->title,
            $application->leader->name,
            $application->leader->email,
            $teamSize,
            $teamMembers ?: 'Только лидер',
            $application->created_at->format('d.m.Y H:i'),
        ];
    }

    /**
     * @param  Worksheet  $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
