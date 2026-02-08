<?php

declare(strict_types=1);

namespace App\Services\Simulators\BankSimulator;

use InvalidArgumentException;

class DialogueService
{

    /**
     * Get dialogue stage configuration
     *
     * @param string $stageId Stage identifier (e.g., 'greeting', 'credit_inquiry')
     * @param array<string, mixed> $context Additional context data
     * @return array<string, mixed> Stage configuration with client_message, user_options, next_stage, required_data
     * @throws InvalidArgumentException If stage not found
     */
    public function getStage(string $stageId, array $context = []): array
    {
        $stages = config('simulators.bank_simulator_dialogue.stages', []);

        if (!isset($stages[$stageId])) {
            throw new InvalidArgumentException("Dialogue stage '{$stageId}' not found");
        }

        return $stages[$stageId];
    }

    /**
     * Process user choice and determine next stage
     *
     * @param string $choiceId Choice identifier (e.g., 'credit_card', 'deposit')
     * @param string $currentStageId Current stage identifier
     * @param array<string, mixed> $context Additional context data
     * @return string Next stage identifier
     * @throws InvalidArgumentException If choice or stage not found
     */
    public function processUserChoice(string $choiceId, string $currentStageId, array $context = []): string
    {
        $stage = $this->getStage($currentStageId, $context);
        $nextStage = $stage['next_stage'] ?? null;

        if ($nextStage === null) {
            throw new InvalidArgumentException("Stage '{$currentStageId}' has no next_stage configuration");
        }

        // If next_stage is a string, it's a direct transition
        if (is_string($nextStage)) {
            return $nextStage;
        }

        // If next_stage is an array, it's a mapping of choices to stages
        if (is_array($nextStage)) {
            if (!isset($nextStage[$choiceId])) {
                throw new InvalidArgumentException("Choice '{$choiceId}' not found in next_stage mapping for stage '{$currentStageId}'");
            }

            return $nextStage[$choiceId];
        }

        throw new InvalidArgumentException("Invalid next_stage configuration for stage '{$currentStageId}'");
    }

    /**
     * Get response options for a stage
     *
     * @param string $stageId Stage identifier
     * @param array<string, mixed> $context Additional context data
     * @return array<int, array<string, string>> Array of options with 'id' and 'text'
     */
    public function getResponseOptions(string $stageId, array $context = []): array
    {
        $stage = $this->getStage($stageId, $context);

        return $stage['user_options'] ?? [];
    }

    /**
     * Get required data fields for a stage
     *
     * @param string $stageId Stage identifier
     * @param array<string, mixed> $context Additional context data
     * @return array<int, string> Array of required field names
     */
    public function getRequiredData(string $stageId, array $context = []): array
    {
        $stage = $this->getStage($stageId, $context);

        return $stage['required_data'] ?? [];
    }

    /**
     * Check if stage is final (completion stage)
     *
     * @param string $stageId Stage identifier
     * @param array<string, mixed> $context Additional context data
     * @return bool True if stage is final
     */
    public function isFinalStage(string $stageId, array $context = []): bool
    {
        $stage = $this->getStage($stageId, $context);

        return (bool) ($stage['is_final'] ?? false);
    }

    /**
     * Check if stage should show calculations
     *
     * @param string $stageId Stage identifier
     * @param array<string, mixed> $context Additional context data
     * @return bool True if stage should show calculations
     */
    public function shouldShowCalculations(string $stageId, array $context = []): bool
    {
        $stage = $this->getStage($stageId, $context);

        return (bool) ($stage['show_calculations'] ?? false);
    }

}
