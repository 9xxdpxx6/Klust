<?php

declare(strict_types=1);

namespace App\Services\Simulators\BankSimulator;

use InvalidArgumentException;

/**
 * Service for processing dialogue actions
 * 
 * Handles execution of various action types defined in dialogue configuration:
 * - add_score_points: Add points to user score
 * - show_message: Show a message to client/user
 * - update_client_data: Update client data fields
 * - open_calculator: Open credit/deposit calculator
 * - open_phone: Open phone dialog
 * - open_documents: Open documents dialog
 * - calculate_scoring: Calculate credit scoring
 * - calculate_credit: Calculate credit parameters
 * - calculate_deposit: Calculate deposit result
 * - check_condition: Conditionally execute actions
 */
class ActionProcessor
{
    public function __construct(
        private readonly ScoringService $scoringService,
        private readonly CreditCalculatorService $creditCalculatorService,
        private readonly DepositCalculatorService $depositCalculatorService
    ) {
    }

    /**
     * Process array of actions
     *
     * @param array<int, array<string, mixed>> $actions Array of action configurations
     * @param array<string, mixed> $context Context data (session state, client data, etc.)
     * @return array<string, mixed> Result with success, updates, effects, messages
     */
    public function processActions(array $actions, array $context = []): array
    {
        $result = [
            'success' => true,
            'updates' => [],
            'effects' => [],
            'messages' => [],
            'errors' => [],
        ];

        foreach ($actions as $action) {
            if (!isset($action['type'])) {
                $result['errors'][] = 'Action missing type field';
                $result['success'] = false;
                continue;
            }

            try {
                $actionResult = $this->processSingleAction($action, $context);

                // Merge results (deep merge: scalars replace, arrays merge recursively)
                if (isset($actionResult['updates'])) {
                    $result['updates'] = $this->deepMergeUpdates($result['updates'], $actionResult['updates']);

                    // Feed updates back into context so subsequent actions see them
                    // (e.g. calculate_credit needs scoring results from calculate_scoring)
                    $context = $this->deepMergeUpdates($context, $actionResult['updates']);
                }
                if (isset($actionResult['effects'])) {
                    $result['effects'] = array_merge($result['effects'], $actionResult['effects']);
                }
                if (isset($actionResult['messages'])) {
                    $result['messages'] = array_merge($result['messages'], $actionResult['messages']);
                }
                if (isset($actionResult['success']) && !$actionResult['success']) {
                    $result['success'] = false;
                }
                if (isset($actionResult['errors'])) {
                    $result['errors'] = array_merge($result['errors'], $actionResult['errors']);
                }
            } catch (\Exception $e) {
                $result['errors'][] = $e->getMessage();
                $result['success'] = false;
            }
        }

        return $result;
    }

    /**
     * Deep merge: scalars replace (not turned into arrays),
     * associative arrays are recursively merged.
     *
     * @param array<string, mixed> $base
     * @param array<string, mixed> $override
     * @return array<string, mixed>
     */
    private function deepMergeUpdates(array $base, array $override): array
    {
        foreach ($override as $key => $value) {
            if (
                is_array($value) && !array_is_list($value)
                && isset($base[$key]) && is_array($base[$key]) && !array_is_list($base[$key])
            ) {
                $base[$key] = $this->deepMergeUpdates($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }

    /**
     * Process single action
     *
     * @param array<string, mixed> $action Action configuration
     * @param array<string, mixed> $context Context data
     * @return array<string, mixed> Action result
     */
    private function processSingleAction(array $action, array $context): array
    {
        $type = $action['type'];

        return match ($type) {
            'add_score_points' => $this->processAddScorePoints($action, $context),
            'show_message' => $this->processShowMessage($action, $context),
            'update_client_data' => $this->processUpdateClientData($action, $context),
            'open_calculator' => $this->processOpenCalculator($action, $context),
            'open_phone' => $this->processOpenPhone($action, $context),
            'open_documents' => $this->processOpenDocuments($action, $context),
            'calculate_scoring' => $this->processCalculateScoring($action, $context),
            'calculate_credit' => $this->processCalculateCredit($action, $context),
            'calculate_deposit' => $this->processCalculateDeposit($action, $context),
            'check_condition' => $this->processCheckCondition($action, $context),
            default => [
                'success' => false,
                'errors' => ["Unknown action type: {$type}"],
            ],
        };
    }

    /**
     * Process add_score_points action
     *
     * @param array<string, mixed> $action Action configuration: {type: 'add_score_points', points: int}
     * @param array<string, mixed> $context Context data
     * @return array<string, mixed> Result
     */
    private function processAddScorePoints(array $action, array $context): array
    {
        if (!isset($action['points']) || !is_numeric($action['points'])) {
            return [
                'success' => false,
                'errors' => ['add_score_points action requires points field'],
            ];
        }

        $points = (int) $action['points'];
        $currentScore = (int) ($context['score'] ?? 0);
        
        // Ensure score_history is an array
        $scoreHistory = $context['score_history'] ?? [];
        if (!is_array($scoreHistory)) {
            $scoreHistory = [];
        }

        $entry = [
            'points' => $points,
            'reason' => $action['reason'] ?? 'Action reward',
            'timestamp' => now()->toIso8601String(),
        ];

        // Track evaluation category if provided
        if (isset($action['category']) && is_string($action['category'])) {
            $entry['category'] = $action['category'];
        }

        $scoreHistory[] = $entry;

        return [
            'success' => true,
            'updates' => [
                'score' => $currentScore + $points,
                'score_history' => $scoreHistory,
            ],
        ];
    }

    /**
     * Process show_message action
     *
     * @param array<string, mixed> $action Action configuration: {type: 'show_message', message: string, role: 'client'|'user'}
     * @param array<string, mixed> $context Context data
     * @return array<string, mixed> Result
     */
    private function processShowMessage(array $action, array $context): array
    {
        if (!isset($action['message'])) {
            return [
                'success' => false,
                'errors' => ['show_message action requires message field'],
            ];
        }

        $role = $action['role'] ?? 'client';

        return [
            'success' => true,
            'effects' => [
                [
                    'type' => 'show_message',
                    'message' => $action['message'],
                    'role' => $role,
                ],
            ],
        ];
    }

    /**
     * Process update_client_data action
     *
     * @param array<string, mixed> $action Action configuration: {type: 'update_client_data', field: string, value: mixed}
     * @param array<string, mixed> $context Context data
     * @return array<string, mixed> Result
     */
    private function processUpdateClientData(array $action, array $context): array
    {
        if (!isset($action['field']) || !isset($action['value'])) {
            return [
                'success' => false,
                'errors' => ['update_client_data action requires field and value'],
            ];
        }

        $field = $action['field'];
        $value = $action['value'];

        return [
            'success' => true,
            'updates' => [
                'client' => [
                    $field => $value,
                ],
            ],
        ];
    }

    /**
     * Process open_calculator action
     *
     * @param array<string, mixed> $action Action configuration: {type: 'open_calculator', calculator: 'credit'|'deposit'}
     * @param array<string, mixed> $context Context data
     * @return array<string, mixed> Result
     */
    private function processOpenCalculator(array $action, array $context): array
    {
        if (!isset($action['calculator'])) {
            return [
                'success' => false,
                'errors' => ['open_calculator action requires calculator field'],
            ];
        }

        $calculator = $action['calculator'];
        if (!in_array($calculator, ['credit', 'deposit'], true)) {
            return [
                'success' => false,
                'errors' => ["Invalid calculator type: {$calculator}"],
            ];
        }

        return [
            'success' => true,
            'effects' => [
                [
                    'type' => 'open_calculator',
                    'calculator' => $calculator,
                ],
            ],
        ];
    }

    /**
     * Process open_phone action
     *
     * @param array<string, mixed> $action Action configuration: {type: 'open_phone'}
     * @param array<string, mixed> $context Context data
     * @return array<string, mixed> Result
     */
    private function processOpenPhone(array $action, array $context): array
    {
        return [
            'success' => true,
            'effects' => [
                [
                    'type' => 'open_phone',
                ],
            ],
        ];
    }

    /**
     * Process open_documents action
     *
     * @param array<string, mixed> $action Action configuration: {type: 'open_documents'}
     * @param array<string, mixed> $context Context data
     * @return array<string, mixed> Result
     */
    private function processOpenDocuments(array $action, array $context): array
    {
        return [
            'success' => true,
            'effects' => [
                [
                    'type' => 'open_documents',
                ],
            ],
        ];
    }

    /**
     * Process calculate_scoring action
     *
     * @param array<string, mixed> $action Action configuration: {type: 'calculate_scoring'}
     * @param array<string, mixed> $context Context data
     * @return array<string, mixed> Result
     */
    private function processCalculateScoring(array $action, array $context): array
    {
        $clientData = $context['client'] ?? [];

        // Validate required fields
        $requiredFields = ['income', 'expenses', 'age', 'credit_history'];
        foreach ($requiredFields as $field) {
            if (!isset($clientData[$field])) {
                return [
                    'success' => false,
                    'errors' => ["Missing required client data field: {$field}"],
                ];
            }
        }

        try {
            $score = $this->scoringService->calculateCreditScore($clientData);
            $interpretation = $this->scoringService->interpretScore($score);

            return [
                'success' => true,
                'updates' => [
                    'calculations' => [
                        'credit_score' => $score,
                        'decision' => $interpretation['decision'],
                        'interest_rate' => $interpretation['interest_rate'],
                        'limit_multiplier' => $interpretation['limit_multiplier'],
                        'requires_insurance' => $interpretation['requires_insurance'],
                    ],
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['Failed to calculate scoring: ' . $e->getMessage()],
            ];
        }
    }

    /**
     * Process calculate_credit action
     *
     * @param array<string, mixed> $action Action configuration: {type: 'calculate_credit'}
     * @param array<string, mixed> $context Context data
     * @return array<string, mixed> Result
     */
    private function processCalculateCredit(array $action, array $context): array
    {
        $clientData = $context['client'] ?? [];
        $calculations = $context['calculations'] ?? [];
        $dialogueData = $context['dialogue'] ?? [];
        $formData = $dialogueData['formData'] ?? [];

        // Get credit amount from formData or use default
        $creditAmount = $formData['credit_amount'] ?? 500000;
        $creditMonths = $formData['credit_months'] ?? 60;

        // Get interest rate from scoring or use default
        $interestRate = $calculations['interest_rate'] ?? $calculations['scoring_decision']['interest_rate'] ?? 15.0;

        if ($interestRate === null) {
            return [
                'success' => false,
                'errors' => ['Cannot calculate credit: interest rate not available'],
            ];
        }

        try {
            $monthlyPayment = $this->creditCalculatorService->calculateAnnuityPayment(
                (float) $creditAmount,
                (int) $creditMonths,
                (float) $interestRate
            );

            $totalPayment = $this->creditCalculatorService->calculateTotalPayment($monthlyPayment, (int) $creditMonths);
            $overpayment = $this->creditCalculatorService->calculateOverpayment($totalPayment, (float) $creditAmount);

            // Calculate credit limit based on scoring
            $income = (float) ($clientData['income'] ?? 0);
            $limitMultiplier = $calculations['limit_multiplier'] ?? 1.0;
            $creditLimit = $income * 10 * $limitMultiplier; // Base limit is 10x monthly income

            return [
                'success' => true,
                'updates' => [
                    'calculations' => [
                        'credit_limit' => round($creditLimit, 2),
                        'monthly_payment' => round($monthlyPayment, 2),
                        'total_payment' => round($totalPayment, 2),
                        'overpayment' => round($overpayment, 2),
                    ],
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['Failed to calculate credit: ' . $e->getMessage()],
            ];
        }
    }

    /**
     * Process calculate_deposit action
     *
     * @param array<string, mixed> $action Action configuration: {type: 'calculate_deposit'}
     * @param array<string, mixed> $context Context data
     * @return array<string, mixed> Result
     */
    private function processCalculateDeposit(array $action, array $context): array
    {
        $dialogueData = $context['dialogue'] ?? [];
        $formData = $dialogueData['formData'] ?? [];

        // Get deposit parameters from formData
        $depositAmount = $formData['deposit_amount'] ?? null;
        $depositPeriod = $formData['deposit_period'] ?? null;

        if ($depositAmount === null || $depositPeriod === null) {
            return [
                'success' => false,
                'errors' => ['Cannot calculate deposit: missing deposit_amount or deposit_period'],
            ];
        }

        // Convert months to years (assuming deposit_period is in months)
        $years = (int) ($depositPeriod / 12);
        $annualRate = 8.0; // Default rate, could be from config

        try {
            $finalAmount = $this->depositCalculatorService->calculateDeposit(
                (float) $depositAmount,
                $annualRate,
                max(1, $years),
                12 // Monthly capitalization
            );

            return [
                'success' => true,
                'updates' => [
                    'calculations' => [
                        'deposit_result' => round($finalAmount, 2),
                        'deposit_income' => round($finalAmount - (float) $depositAmount, 2),
                        'deposit_rate' => $annualRate,
                    ],
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['Failed to calculate deposit: ' . $e->getMessage()],
            ];
        }
    }

    /**
     * Process check_condition action
     *
     * @param array<string, mixed> $action Action configuration: {type: 'check_condition', field: string, operator: string, value: mixed, then: array}
     * @param array<string, mixed> $context Context data
     * @return array<string, mixed> Result
     */
    private function processCheckCondition(array $action, array $context): array
    {
        if (!isset($action['field']) || !isset($action['operator']) || !isset($action['value'])) {
            return [
                'success' => false,
                'errors' => ['check_condition action requires field, operator, and value'],
            ];
        }

        $field = $action['field'];
        $operator = $action['operator'];
        $value = $action['value'];

        // Get field value from context (supports nested fields like 'client.income')
        $fieldValue = $this->getNestedValue($context, $field);

        // Check condition
        $conditionMet = match ($operator) {
            '>' => $fieldValue > $value,
            '>=' => $fieldValue >= $value,
            '<' => $fieldValue < $value,
            '<=' => $fieldValue <= $value,
            '==' => $fieldValue == $value,
            '===' => $fieldValue === $value,
            '!=' => $fieldValue != $value,
            '!==' => $fieldValue !== $value,
            default => false,
        };

        // If condition is met and 'then' actions are defined, process them
        if ($conditionMet && isset($action['then']) && is_array($action['then'])) {
            return $this->processActions($action['then'], $context);
        }

        return [
            'success' => true,
        ];
    }

    /**
     * Get nested value from array using dot notation
     *
     * @param array<string, mixed> $array Array to search
     * @param string $path Dot-notation path (e.g., 'client.income')
     * @return mixed Value or null if not found
     */
    private function getNestedValue(array $array, string $path): mixed
    {
        $keys = explode('.', $path);
        $value = $array;

        foreach ($keys as $key) {
            if (!is_array($value) || !isset($value[$key])) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }
}
