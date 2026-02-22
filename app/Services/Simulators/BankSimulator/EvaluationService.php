<?php

declare(strict_types=1);

namespace App\Services\Simulators\BankSimulator;

class EvaluationService
{
    /**
     * Evaluation categories with weights (must sum to 1.0)
     */
    private const CATEGORY_WEIGHTS = [
        'correctness' => 0.40,      // Корректность решения (сбор данных, скоринг)
        'service_quality' => 0.30,   // Качество обслуживания (эмпатия, коммуникация)
        'compliance' => 0.30,        // Соблюдение регламентов (БКИ, документы, процедуры)
    ];

    /**
     * Category labels for display
     */
    private const CATEGORY_LABELS = [
        'correctness' => 'Корректность решения',
        'service_quality' => 'Качество обслуживания',
        'compliance' => 'Соблюдение регламентов',
    ];

    /**
     * Evaluate a completed simulator session
     *
     * @param array<string, mixed> $sessionState Session state with score_history
     * @param int $maxScore Maximum possible score for the dialogue
     * @return array<string, mixed> Evaluation result with breakdown
     */
    public function evaluate(array $sessionState, int $maxScore = 100): array
    {
        $scoreHistory = $sessionState['score_history'] ?? [];
        $rawScore = (int) ($sessionState['score'] ?? 0);

        // Calculate per-category totals
        $categoryScores = $this->calculateCategoryScores($scoreHistory);

        // Normalize total score to 0-100
        $normalizedScore = $maxScore > 0
            ? (int) round(max(0, $rawScore) / $maxScore * 100)
            : 0;
        $normalizedScore = min(100, $normalizedScore);

        // Build category breakdown
        $breakdown = $this->buildCategoryBreakdown($categoryScores, $maxScore);

        // Calculate weighted score
        $weightedScore = $this->calculateWeightedScore($breakdown);

        return [
            'raw_score' => $rawScore,
            'max_score' => $maxScore,
            'normalized_score' => $normalizedScore,
            'weighted_score' => $weightedScore,
            'grade' => $this->getGrade($weightedScore),
            'grade_label' => $this->getGradeLabel($weightedScore),
            'categories' => $breakdown,
            'feedback' => $this->generateFeedback($breakdown, $weightedScore),
        ];
    }

    /**
     * Calculate scores per category from score_history
     *
     * @param array<int, array<string, mixed>> $scoreHistory
     * @return array<string, array{earned: int, lost: int, total: int}>
     */
    private function calculateCategoryScores(array $scoreHistory): array
    {
        $categories = [];
        foreach (array_keys(self::CATEGORY_WEIGHTS) as $category) {
            $categories[$category] = ['earned' => 0, 'lost' => 0, 'total' => 0];
        }
        // Fallback for actions without category
        $categories['uncategorized'] = ['earned' => 0, 'lost' => 0, 'total' => 0];

        foreach ($scoreHistory as $entry) {
            $points = (int) ($entry['points'] ?? 0);
            $category = $entry['category'] ?? 'uncategorized';

            if (!isset($categories[$category])) {
                $category = 'uncategorized';
            }

            $categories[$category]['total'] += $points;
            if ($points >= 0) {
                $categories[$category]['earned'] += $points;
            } else {
                $categories[$category]['lost'] += abs($points);
            }
        }

        // Distribute uncategorized points proportionally
        $uncategorized = $categories['uncategorized'];
        if ($uncategorized['total'] !== 0) {
            foreach (array_keys(self::CATEGORY_WEIGHTS) as $cat) {
                $weight = self::CATEGORY_WEIGHTS[$cat];
                $categories[$cat]['earned'] += (int) round($uncategorized['earned'] * $weight);
                $categories[$cat]['lost'] += (int) round($uncategorized['lost'] * $weight);
                $categories[$cat]['total'] += (int) round($uncategorized['total'] * $weight);
            }
        }
        unset($categories['uncategorized']);

        return $categories;
    }

    /**
     * Build category breakdown with percentages
     *
     * @param array<string, array{earned: int, lost: int, total: int}> $categoryScores
     * @param int $maxScore
     * @return array<string, array<string, mixed>>
     */
    private function buildCategoryBreakdown(array $categoryScores, int $maxScore): array
    {
        $breakdown = [];

        foreach (self::CATEGORY_WEIGHTS as $category => $weight) {
            $data = $categoryScores[$category] ?? ['earned' => 0, 'lost' => 0, 'total' => 0];
            $categoryMax = (int) round($maxScore * $weight);

            // Percentage: how much of the category max was earned (net)
            $percentage = $categoryMax > 0
                ? (int) round(max(0, $data['total']) / $categoryMax * 100)
                : 0;
            $percentage = min(100, $percentage);

            $breakdown[$category] = [
                'label' => self::CATEGORY_LABELS[$category] ?? $category,
                'weight' => $weight,
                'weight_percent' => (int) ($weight * 100),
                'earned' => $data['earned'],
                'lost' => $data['lost'],
                'net' => $data['total'],
                'max' => $categoryMax,
                'percentage' => $percentage,
                'grade' => $this->getGrade($percentage),
            ];
        }

        return $breakdown;
    }

    /**
     * Calculate weighted score from category breakdown
     *
     * @param array<string, array<string, mixed>> $breakdown
     * @return int Weighted score 0-100
     */
    private function calculateWeightedScore(array $breakdown): int
    {
        $weightedSum = 0.0;
        $totalWeight = 0.0;

        foreach ($breakdown as $category => $data) {
            $weight = self::CATEGORY_WEIGHTS[$category] ?? 0;
            $weightedSum += $data['percentage'] * $weight;
            $totalWeight += $weight;
        }

        if ($totalWeight <= 0) {
            return 0;
        }

        return (int) round($weightedSum / $totalWeight);
    }

    /**
     * Get letter grade
     */
    private function getGrade(int $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 75 => 'B',
            $score >= 60 => 'C',
            $score >= 40 => 'D',
            default => 'F',
        };
    }

    /**
     * Get grade label in Russian
     */
    private function getGradeLabel(int $score): string
    {
        return match (true) {
            $score >= 90 => 'Отлично',
            $score >= 75 => 'Хорошо',
            $score >= 60 => 'Удовлетворительно',
            $score >= 40 => 'Ниже среднего',
            default => 'Неудовлетворительно',
        };
    }

    /**
     * Generate feedback based on category breakdown
     *
     * @param array<string, array<string, mixed>> $breakdown
     * @param int $weightedScore
     * @return array<int, string>
     */
    private function generateFeedback(array $breakdown, int $weightedScore): array
    {
        $feedback = [];

        foreach ($breakdown as $category => $data) {
            $pct = $data['percentage'];
            $label = $data['label'];

            if ($pct >= 90) {
                $feedback[] = "✅ {$label}: отличный результат ({$pct}%)";
            } elseif ($pct >= 70) {
                $feedback[] = "👍 {$label}: хороший результат ({$pct}%), есть небольшие моменты для улучшения";
            } elseif ($pct >= 50) {
                $feedback[] = "⚠️ {$label}: средний результат ({$pct}%), рекомендуется повторить";
            } else {
                $feedback[] = "❌ {$label}: низкий результат ({$pct}%), требует значительного улучшения";
            }
        }

        return $feedback;
    }

    /**
     * Get category weights for frontend display
     *
     * @return array<string, array{weight: float, label: string}>
     */
    public function getCategoryInfo(): array
    {
        $info = [];
        foreach (self::CATEGORY_WEIGHTS as $category => $weight) {
            $info[$category] = [
                'weight' => $weight,
                'label' => self::CATEGORY_LABELS[$category] ?? $category,
            ];
        }
        return $info;
    }
}
