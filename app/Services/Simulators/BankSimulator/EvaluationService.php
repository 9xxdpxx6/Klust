<?php

declare(strict_types=1);

namespace App\Services\Simulators\BankSimulator;

class EvaluationService
{
    /**
     * Category labels for display
     */
    private const CATEGORY_LABELS = [
        'correctness' => 'Корректность решения',
        'service_quality' => 'Качество обслуживания',
        'compliance' => 'Соблюдение регламентов',
    ];

    /**
     * Get evaluation category weights from config (single source of truth).
     *
     * @return array<string, float>
     */
    private function getCategoryWeights(): array
    {
        return config('simulators.bank_simulator.evaluation_weights', [
            'correctness' => 0.40,
            'service_quality' => 0.30,
            'compliance' => 0.30,
        ]);
    }

    /**
     * Normalize a raw score to 0-100 using maxScore.
     * Single source of truth — no other code should duplicate this logic.
     */
    public static function normalizeScore(int $rawScore, int $maxScore): int
    {
        if ($maxScore <= 0) {
            return 0;
        }

        return min(100, (int) round(max(0, $rawScore) / $maxScore * 100));
    }

    /**
     * Evaluate a single dialogue variant.
     *
     * @param  array<string, mixed>  $variantData  Variant state with score_history, score, max_score
     * @return array<string, mixed> Evaluation result with breakdown
     */
    public function evaluate(array $variantData, int $maxScore = 100): array
    {
        $scoreHistory = $variantData['score_history'] ?? [];
        $rawScore = (int) ($variantData['score'] ?? 0);

        $categoryScores = $this->calculateCategoryScores($scoreHistory);
        $normalizedScore = self::normalizeScore($rawScore, $maxScore);
        $breakdown = $this->buildCategoryBreakdown($categoryScores, $maxScore);
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
     * Evaluate the full session across all completed variants.
     *
     * Aggregates score_history from each variant in variants_progress,
     * then computes a combined weighted score.
     *
     * @param  array<string, mixed>  $sessionState  Full session state
     * @return array<string, mixed> Combined evaluation
     */
    public function evaluateSession(array $sessionState): array
    {
        $variantsProgress = $sessionState['variants_progress'] ?? [];
        $perVariant = [];
        $aggregatedScoreHistory = [];
        $totalRaw = 0;
        $totalMax = 0;

        foreach ($variantsProgress as $variant => $data) {
            if (($data['status'] ?? '') !== 'completed') {
                continue;
            }

            $raw = (int) ($data['score'] ?? 0);
            $max = (int) ($data['max_score'] ?? 100);
            $history = $data['score_history'] ?? [];

            // Evaluate individual variant
            $perVariant[$variant] = $this->evaluate($data, $max);

            // Accumulate for aggregated analysis
            $totalRaw += $raw;
            $totalMax += $max;
            $aggregatedScoreHistory = array_merge($aggregatedScoreHistory, $history);
        }

        // Overall category breakdown from aggregated score_history
        $categoryScores = $this->calculateCategoryScores($aggregatedScoreHistory);
        $overallBreakdown = $this->buildCategoryBreakdown($categoryScores, max(1, $totalMax));
        $overallWeighted = $this->calculateWeightedScore($overallBreakdown);

        // Overall normalized score (same logic as frontend: sum, avg if > 100)
        $normalizedScores = array_map(
            fn ($ev) => $ev['normalized_score'],
            $perVariant
        );
        $sum = array_sum($normalizedScores);
        $count = count($normalizedScores);
        $finalScore = $count > 0
            ? ($sum <= 100 ? (int) round($sum) : (int) round($sum / $count))
            : 0;

        return [
            'final_score' => $finalScore,
            'weighted_score' => $overallWeighted,
            'grade' => $this->getGrade($overallWeighted),
            'grade_label' => $this->getGradeLabel($overallWeighted),
            'categories' => $overallBreakdown,
            'feedback' => $this->generateFeedback($overallBreakdown, $overallWeighted),
            'variants' => $perVariant,
            'variants_completed' => count($perVariant),
            'variants_total' => 4,
        ];
    }

    /**
     * Calculate scores per category from score_history
     *
     * @param  array<int, array<string, mixed>>  $scoreHistory
     * @return array<string, array{earned: int, lost: int, total: int}>
     */
    private function calculateCategoryScores(array $scoreHistory): array
    {
        $weights = $this->getCategoryWeights();

        $categories = [];
        foreach (array_keys($weights) as $category) {
            $categories[$category] = ['earned' => 0, 'lost' => 0, 'total' => 0];
        }
        $categories['uncategorized'] = ['earned' => 0, 'lost' => 0, 'total' => 0];

        foreach ($scoreHistory as $entry) {
            $points = (int) ($entry['points'] ?? 0);
            $category = $entry['category'] ?? 'uncategorized';

            if (! isset($categories[$category])) {
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
            foreach ($weights as $cat => $weight) {
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
     * @param  array<string, array{earned: int, lost: int, total: int}>  $categoryScores
     * @return array<string, array<string, mixed>>
     */
    private function buildCategoryBreakdown(array $categoryScores, int $maxScore): array
    {
        $weights = $this->getCategoryWeights();
        $breakdown = [];

        foreach ($weights as $category => $weight) {
            $data = $categoryScores[$category] ?? ['earned' => 0, 'lost' => 0, 'total' => 0];
            $categoryMax = (int) round($maxScore * $weight);

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
     * @param  array<string, array<string, mixed>>  $breakdown
     * @return int Weighted score 0-100
     */
    private function calculateWeightedScore(array $breakdown): int
    {
        $weights = $this->getCategoryWeights();
        $weightedSum = 0.0;
        $totalWeight = 0.0;

        foreach ($breakdown as $category => $data) {
            $weight = $weights[$category] ?? 0;
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
     * @param  array<string, array<string, mixed>>  $breakdown
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
        $weights = $this->getCategoryWeights();
        $info = [];
        foreach ($weights as $category => $weight) {
            $info[$category] = [
                'weight' => $weight,
                'label' => self::CATEGORY_LABELS[$category] ?? $category,
            ];
        }

        return $info;
    }
}
