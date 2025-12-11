<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\CaseModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpdateCaseStatusByDeadline implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $caseId
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Проверяем флаг отмены (для Redis и других драйверов)
        $isCancelled = Cache::get("case_status_job_cancelled_{$this->caseId}");
        if ($isCancelled) {
            Log::info('Job cancelled, skipping execution', [
                'case_id' => $this->caseId,
            ]);
            
            // Clean up
            Cache::forget("case_status_job_{$this->caseId}");
            Cache::forget("case_status_job_cancelled_{$this->caseId}");
            
            return;
        }

        $case = CaseModel::find($this->caseId);

        if (! $case) {
            Log::warning('Case not found for status update job', [
                'case_id' => $this->caseId,
            ]);

            return;
        }

        // Проверяем, что кейс все еще активен и дедлайн прошел
        // Также проверяем, что дедлайн не изменился (защита от устаревших задач)
        if ($case->status === 'active' && $case->deadline && $case->deadline->isPast()) {
            // Дополнительная проверка: убеждаемся, что задача все еще актуальна
            $jobInfo = Cache::get("case_status_job_{$this->caseId}");
            
            if ($jobInfo && isset($jobInfo['deadline'])) {
                $scheduledDeadline = \Carbon\Carbon::parse($jobInfo['deadline']);
                // Если дедлайн изменился, не обновляем статус
                if ($scheduledDeadline->format('Y-m-d') !== $case->deadline->format('Y-m-d')) {
                    Log::info('Case deadline changed, skipping status update', [
                        'case_id' => $this->caseId,
                        'scheduled_deadline' => $scheduledDeadline->format('Y-m-d'),
                        'current_deadline' => $case->deadline->format('Y-m-d'),
                    ]);

                    // Clean up
                    Cache::forget("case_status_job_{$this->caseId}");
                    Cache::forget("case_status_job_cancelled_{$this->caseId}");

                    return;
                }
            }

            $case->update(['status' => 'completed']);

            // Удаляем задачу из кеша
            Cache::forget("case_status_job_{$this->caseId}");
            Cache::forget("case_status_job_cancelled_{$this->caseId}");

            Log::info('Case status automatically updated to completed by deadline', [
                'case_id' => $this->caseId,
                'deadline' => $case->deadline->format('Y-m-d'),
            ]);
        } else {
            // Кейс уже не активен или дедлайн не прошел - удаляем из кеша
            Cache::forget("case_status_job_{$this->caseId}");
            Cache::forget("case_status_job_cancelled_{$this->caseId}");
        }
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return "update_case_status_{$this->caseId}";
    }
}
