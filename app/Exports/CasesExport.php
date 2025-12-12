<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\CaseModel;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CasesExport implements FromView, WithColumnWidths, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function view(): View
    {
        $query = CaseModel::with(['partner', 'skills']);

        // Apply filters if provided
        if (! empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (! empty($this->filters['partner_id'])) {
            $query->where('partner_id', $this->filters['partner_id']);
        }

        if (! empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (! empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        $cases = $query->get();

        return view('exports.cases', [
            'cases' => $cases,
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,  // ID
            'B' => 30,  // Название
            'C' => 28,  // Описание (максимум ~200px, 1 единица Excel ≈ 7px)
            'D' => 25,  // Партнер
            'E' => 15,  // Статус
            'F' => 15,  // Размер команды
            'G' => 18,  // Срок выполнения
            'H' => 20,  // Дата создания
            'I' => 40,  // Требуемые навыки
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]], // Make header row bold
        ];
    }
}
