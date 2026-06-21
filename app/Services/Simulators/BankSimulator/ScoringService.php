<?php

declare(strict_types=1);

namespace App\Services\Simulators\BankSimulator;

use InvalidArgumentException;

class ScoringService
{
    /**
     * Calculate credit score based on client data
     *
     * @param  array<string, mixed>  $clientData  Client data: income, expenses, age, credit_history, has_deposit
     * @param  array<string, mixed>  $creditParams  Credit parameters (reserved for future use)
     * @return float Score in range [0, 1]
     *
     * @throws InvalidArgumentException If required client data is missing or invalid
     */
    public function calculateCreditScore(array $clientData, array $creditParams = []): float
    {
        // Validate required fields
        $this->validateClientData($clientData);

        $income = (float) $clientData['income'];
        $expenses = (float) $clientData['expenses'];
        $age = (int) $clientData['age'];
        $creditHistory = (string) $clientData['credit_history'];
        $hasDeposit = (bool) ($clientData['has_deposit'] ?? false);

        // Validate values
        if ($income <= 0) {
            throw new InvalidArgumentException('Income must be greater than 0');
        }
        if ($expenses < 0) {
            throw new InvalidArgumentException('Expenses cannot be negative');
        }
        if ($expenses > $income) {
            throw new InvalidArgumentException('Expenses cannot exceed income');
        }
        if ($age < 18 || $age > 100) {
            throw new InvalidArgumentException('Age must be between 18 and 100');
        }

        // Get weights from config
        $weights = $this->getWeights();

        // Calculate income coefficient (normalized to 50000)
        $incomeCoeff = $income / 50000;

        // Calculate expense coefficient (ratio of expenses to income)
        $expenseCoeff = $expenses / $income;

        // Calculate age coefficient (optimal age is 30)
        $ageCoeff = abs(30 - $age) / 30;

        // Calculate credit history coefficient
        $creditHistoryCoeff = $this->getCreditHistoryCoefficient($creditHistory);

        // Calculate bonuses/penalties
        $bonuses = 0.0;
        if ($hasDeposit) {
            $bonuses += 0.2;
        }

        // Calculate total score
        $score = ($incomeCoeff * $weights['income']) -
                 ($expenseCoeff * $weights['expenses']) -
                 ($ageCoeff * $weights['age']) +
                 ($creditHistoryCoeff * $weights['credit_history']) +
                 $bonuses;

        // Clamp score to [0, 1] range
        return max(0.0, min(1.0, $score));
    }

    /**
     * Interpret score and return decision with parameters
     *
     * Interest rate is calculated dynamically based on the score:
     *   score 1.0 → ~10%,  score 0.8 → ~13%,  score 0.5 → ~19%,  score 0.3 → ~22%
     *
     * @param  float  $score  Credit score in range [0, 1]
     * @return array<string, mixed> Decision array with: decision, interest_rate, limit_multiplier, requires_insurance
     */
    public function interpretScore(float $score): array
    {
        if ($score < 0 || $score > 1) {
            throw new InvalidArgumentException('Score must be in range [0, 1]');
        }

        $thresholds = config('simulators.bank_simulator.scoring.thresholds', [
            'auto_approve' => 0.8,
            'approve_with_conditions' => 0.5,
            'manual_review' => 0.3,
            'auto_reject' => 0.0,
        ]);

        // Dynamic interest rate: 24% at score=0.3, 10% at score=1.0
        // Formula: rate = 24 - 14 * score  (linear interpolation)
        $dynamicRate = round(24.0 - 14.0 * $score, 1);

        return match (true) {
            $score >= $thresholds['auto_approve'] => [
                'decision' => 'auto_approve',
                'interest_rate' => $dynamicRate,
                'limit_multiplier' => 1.2 + ($score - 0.8) * 1.5,
                'requires_insurance' => false,
            ],
            $score >= $thresholds['approve_with_conditions'] => [
                'decision' => 'approve_with_conditions',
                'interest_rate' => $dynamicRate,
                'limit_multiplier' => 0.8 + ($score - 0.5) * 1.33,
                'requires_insurance' => true,
            ],
            $score >= $thresholds['manual_review'] => [
                'decision' => 'manual_review',
                'interest_rate' => $dynamicRate,
                'limit_multiplier' => 0.5,
                'requires_insurance' => true,
            ],
            default => [
                'decision' => 'auto_reject',
                'interest_rate' => null,
                'limit_multiplier' => null,
                'requires_insurance' => null,
            ],
        };
    }

    /**
     * Get scoring weights from configuration
     *
     * @return array<string, float> Weights array: income, expenses, age, credit_history
     */
    public function getWeights(): array
    {
        return config('simulators.bank_simulator.scoring.weights', [
            'income' => 0.3,
            'expenses' => 0.25,
            'age' => 0.2,
            'credit_history' => 0.25,
        ]);
    }

    /**
     * Get credit history coefficient based on history type
     *
     * @param  string  $creditHistory  Credit history type: excellent, good, fair, poor, none
     * @return float Coefficient value
     */
    private function getCreditHistoryCoefficient(string $creditHistory): float
    {
        return match ($creditHistory) {
            'excellent' => 1.2,
            'good' => 1.0,
            'fair' => 0.7,
            'poor' => 0.3,
            'none' => 0.5,
            default => 0.5,
        };
    }

    /**
     * Validate client data structure
     *
     * @param  array<string, mixed>  $clientData  Client data to validate
     *
     * @throws InvalidArgumentException If required fields are missing
     */
    private function validateClientData(array $clientData): void
    {
        $requiredFields = ['income', 'expenses', 'age', 'credit_history'];

        foreach ($requiredFields as $field) {
            if (! isset($clientData[$field])) {
                throw new InvalidArgumentException("Missing required field: {$field}");
            }
        }
    }
}
