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
    protected int $userId;
    protected array $filters;

    public function __construct(int $userId, array $filters = [])
    {
        $this->userId = $userId;
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = CaseApplication::whereHas('case', function ($query) {
            $query->where('user_id', $this->userId);
        })
            ->accepted()
            ->with(['leader', 'case', 'teamMembers.user']);

        // Apply date filters if provided
        if (! empty($this->filters['date_from'])) {
            $query->whereDate('submitted_at', '>=', $this->filters['date_from']);
        }

        if (! empty($this->filters['date_to'])) {
            $query->whereDate('submitted_at', '<=', $this->filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

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
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
