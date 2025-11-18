<?php

declare(strict_types=1);

namespace App\Exports;

use App\Services\AnalyticsService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalyticsExport implements FromView, ShouldAutoSize, WithStyles
{
    protected array $filters;

    public function __construct(
        private AnalyticsService $analyticsService,
        array $filters = []
    ) {
        $this->filters = $filters;
    }

    public function view(): View
    {
        $analyticsData = $this->analyticsService->getPartnerAnalytics(
            auth()->user()->partner->id ?? null,
            $this->filters
        );

        return view('exports.analytics', [
            'analytics' => $analyticsData,
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]], // Make header row bold
        ];
    }
}
