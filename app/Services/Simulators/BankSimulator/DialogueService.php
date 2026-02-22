<?php

declare(strict_types=1);

namespace App\Services\Simulators\BankSimulator;

use InvalidArgumentException;

class DialogueService
{
    public function __construct(
        private readonly ActionProcessor $actionProcessor
    ) {
    }

    private const DEFAULT_DIALOGUE_TYPE = 'credit_card';

    private const AVAILABLE_DIALOGUE_TYPES = [
        'credit_card',
        'mortgage',
        'consumer_loan',
        'deposit',
    ];

    /**
     * Get dialogue stage configuration
     *
     * @param string $stageId Stage identifier (e.g., 'greeting', 'credit_inquiry')
     * @param array<string, mixed> $context Additional context data (may contain 'dialogue_type')
     * @return array<string, mixed> Stage configuration with client_message, user_options, next_stage, required_data
     * @throws InvalidArgumentException If stage not found
     */
    public function getStage(string $stageId, array $context = []): array
    {
        $dialogueType = $context['dialogue_type'] ?? self::DEFAULT_DIALOGUE_TYPE;
        $stages = $this->loadStages($dialogueType);

        if (!isset($stages[$stageId])) {
            throw new InvalidArgumentException("Dialogue stage '{$stageId}' not found in dialogue type '{$dialogueType}'");
        }

        return $this->resolveDialogueVariants($stages[$stageId]);
    }

    /**
     * Get list of available dialogue types
     *
     * @return array<int, string>
     */
    public function getAvailableDialogueTypes(): array
    {
        return self::AVAILABLE_DIALOGUE_TYPES;
    }

    /**
     * Get max_score for a dialogue type
     *
     * @param string $dialogueType Dialogue type (e.g., 'credit_card', 'mortgage')
     * @return int Maximum possible score for the dialogue
     */
    public function getMaxScore(string $dialogueType = self::DEFAULT_DIALOGUE_TYPE): int
    {
        if (!in_array($dialogueType, self::AVAILABLE_DIALOGUE_TYPES, true)) {
            $dialogueType = self::DEFAULT_DIALOGUE_TYPE;
        }

        return (int) config("simulators.dialogues.{$dialogueType}.max_score", 100);
    }

    /**
     * Load dialogue stages for a given dialogue type
     *
     * @param string $dialogueType Dialogue type (e.g., 'credit_card', 'mortgage', 'consumer_loan')
     * @return array<string, mixed>
     * @throws InvalidArgumentException If dialogue type not found
     */
    private function loadStages(string $dialogueType): array
    {
        if (!in_array($dialogueType, self::AVAILABLE_DIALOGUE_TYPES, true)) {
            throw new InvalidArgumentException("Unknown dialogue type: '{$dialogueType}'");
        }

        return config("simulators.dialogues.{$dialogueType}.stages", []);
    }

    /**
     * Resolve optional text variants to increase dialogue diversity.
     *
     * Supported keys:
     * - client_message_variants: array<int, string>
     * - user_options[*].text_variants: array<int, string>
     *
     * @param array<string, mixed> $stage
     * @return array<string, mixed>
     */
    private function resolveDialogueVariants(array $stage): array
    {
        if (isset($stage['client_message_variants']) && is_array($stage['client_message_variants'])) {
            $stage['client_message'] = $this->pickVariant($stage['client_message_variants'], $stage['client_message'] ?? '');
        } elseif (isset($stage['client_message']) && is_string($stage['client_message'])) {
            $stage['client_message'] = $this->diversifyText($stage['client_message'], 'client');
        }

        if (!isset($stage['user_options']) || !is_array($stage['user_options'])) {
            return $stage;
        }

        foreach ($stage['user_options'] as $index => $option) {
            if (!is_array($option)) {
                continue;
            }

            if (isset($option['text_variants']) && is_array($option['text_variants'])) {
                $stage['user_options'][$index]['text'] = $this->pickVariant($option['text_variants'], $option['text'] ?? '');
            } elseif (isset($option['text']) && is_string($option['text'])) {
                $stage['user_options'][$index]['text'] = $this->diversifyText($option['text'], 'manager');
            }
        }

        return $stage;
    }

    /**
     * @param array<int, mixed> $variants
     */
    private function pickVariant(array $variants, string $fallback): string
    {
        $normalized = array_values(array_filter($variants, static fn ($item): bool => is_string($item) && trim($item) !== ''));

        if ($normalized === []) {
            return $fallback;
        }

        return $normalized[array_rand($normalized)];
    }

    private function diversifyText(string $text, string $role): string
    {
        $variants = $this->buildVariants($text, $role);

        if ($variants === []) {
            return $text;
        }

        // Keep the base phrase sometimes to avoid over-randomized speech.
        $pool = array_merge([$text], $variants);

        return $pool[array_rand($pool)];
    }

    /**
     * @return array<int, string>
     */
    private function buildVariants(string $text, string $role): array
    {
        $variants = [];

        $templates = [
            'Подскажите ваш официальный доход в месяц?' => [
                'Уточните, пожалуйста, ваш официальный доход в месяц.',
                'Назовите, пожалуйста, официальный ежемесячный доход.',
            ],
            'Сколько составляют ваши ежемесячные расходы?' => [
                'Уточните, пожалуйста, ваши ежемесячные расходы.',
                'Каков средний объём обязательных расходов в месяц?',
            ],
            'Есть ли действующие кредиты?' => [
                'Имеются ли действующие кредиты или рассрочки?',
                'Подтвердите, пожалуйста, наличие действующих кредитных обязательств.',
            ],
            'Были ли просрочки по кредитам?' => [
                'Были ли случаи просрочки по кредитным платежам?',
                'Зафиксированы ли просрочки по кредитам за последний период?',
            ],
            'Хорошо, оформляем.' => [
                'Принято, переходим к оформлению.',
                'Согласовано, начинаем оформление.',
            ],
            'Одну минуту, выполняю расчёт.' => [
                'Минуту, запускаю расчёт параметров.',
                'Один момент, расчёт выполняется.',
            ],
        ];

        if (isset($templates[$text])) {
            $variants = array_merge($variants, $templates[$text]);
        }

        if ($role === 'manager' && str_ends_with($text, '?')) {
            $variants[] = rtrim($text, '?') . ', чтобы рассчитать безопасный лимит?';
            $variants[] = rtrim($text, '?') . ', для корректной оценки риска?';
        }

        if ($role === 'client' && str_contains($text, 'Спасибо')) {
            $variants[] = 'Благодарю, всё понятно.';
            $variants[] = 'Спасибо, условия прозрачны.';
        }

        return array_values(array_unique($variants));
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

    /**
     * Process actions for a stage
     *
     * @param string $stageId Stage identifier
     * @param array<int, array<string, mixed>> $actions Array of actions to process
     * @param array<string, mixed> $context Context data (session state, client data, etc.)
     * @return array<string, mixed> Result with success, updates, effects, messages
     */
    public function processActions(string $stageId, array $actions, array $context = []): array
    {
        return $this->actionProcessor->processActions($actions, $context);
    }

    /**
     * Get actions for a specific option
     *
     * @param string $stageId Stage identifier
     * @param string $optionId Option identifier
     * @param array<string, mixed> $context Additional context data
     * @return array<int, array<string, mixed>> Array of actions for the option
     */
    public function getOptionActions(string $stageId, string $optionId, array $context = []): array
    {
        $stage = $this->getStage($stageId, $context);
        $userOptions = $stage['user_options'] ?? [];

        foreach ($userOptions as $option) {
            if (isset($option['id']) && $option['id'] === $optionId) {
                return $option['actions'] ?? [];
            }
        }

        return [];
    }

    /**
     * Get actions that should be executed when entering a stage
     *
     * @param string $stageId Stage identifier
     * @param array<string, mixed> $context Additional context data
     * @return array<int, array<string, mixed>> Array of actions to execute on stage enter
     */
    public function getStageEnterActions(string $stageId, array $context = []): array
    {
        $stage = $this->getStage($stageId, $context);

        return $stage['on_enter_actions'] ?? [];
    }

}
