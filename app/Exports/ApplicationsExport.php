<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\CaseApplication;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ApplicationsExport implements FromView, ShouldAutoSize, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function view(): View
    {
        $query = CaseApplication::with(['case', 'leader', 'status', 'teamMembers.user', 'case.partner']);

        // Apply filters if provided
        if (! empty($this->filters['status'])) {
            $query->withStatus($this->filters['status']);
        }

        if (! empty($this->filters['case_id'])) {
            $query->where('case_id', $this->filters['case_id']);
        }

        if (! empty($this->filters['partner_id'])) {
            $query->whereHas('case', function ($q) {
                $q->where('partner_id', $this->filters['partner_id']);
            });
        }

        if (! empty($this->filters['date_from'])) {
            $query->whereDate('submitted_at', '>=', $this->filters['date_from']);
        }

        if (! empty($this->filters['date_to'])) {
            $query->whereDate('submitted_at', '<=', $this->filters['date_to']);
        }

        $applications = $query->get();

        return view('exports.applications', [
            'applications' => $applications,
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]], // Make header row bold
        ];
    }
}
