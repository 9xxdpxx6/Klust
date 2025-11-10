<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsExport implements FromView, ShouldAutoSize, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function view(): View
    {
        $query = User::role('student')->with(['profile', 'studentProfile']);

        // Apply filters if provided
        if (!empty($this->filters['specialty'])) {
            $query->whereHas('studentProfile', function ($q) use ($this->filters) {
                $q->where('specialty', $this->filters['specialty']);
            });
        }

        if (!empty($this->filters['course'])) {
            $query->whereHas('studentProfile', function ($q) use ($this->filters) {
                $q->where('course', $this->filters['course']);
            });
        }

        if (!empty($this->filters['group'])) {
            $query->whereHas('studentProfile', function ($q) use ($this->filters) {
                $q->where('group', $this->filters['group']);
            });
        }

        $students = $query->get();

        return view('exports.students', [
            'students' => $students
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]], // Make header row bold
        ];
    }
}