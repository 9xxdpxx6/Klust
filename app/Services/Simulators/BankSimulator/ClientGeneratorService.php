<?php

declare(strict_types=1);

namespace App\Services\Simulators\BankSimulator;

use Illuminate\Support\Facades\Config;

class ClientGeneratorService
{
    /**
     * Generate client data with random parameters based on template.
     *
     * When $dialogueType is provided, the client type is determined by the
     * dialogue_client_mapping config (each variant gets a thematically
     * appropriate client). Falls back to random if no mapping exists.
     *
     * @param string $type Client type ('random', 'student', 'entrepreneur', etc.)
     * @param string|null $dialogueType Dialogue variant (e.g. 'credit_card', 'mortgage')
     * @return array<string, mixed> Client data array
     */
    public function generateClient(string $type = 'random', ?string $dialogueType = null): array
    {
        $templates = config('simulators.bank_simulator.client_templates', []);

        // Resolve type from dialogue mapping when type is 'random' and dialogueType is given
        if ($type === 'random' && $dialogueType !== null) {
            $mapping = config('simulators.bank_simulator.dialogue_client_mapping', []);
            if (isset($mapping[$dialogueType])) {
                $type = $mapping[$dialogueType];
            }
        }

        // If still random, pick random type
        if ($type === 'random') {
            $availableTypes = array_keys($templates);
            if (empty($availableTypes)) {
                throw new \RuntimeException('No client templates available');
            }
            $type = $availableTypes[array_rand($availableTypes)];
        }

        // Get template
        if (!isset($templates[$type])) {
            throw new \InvalidArgumentException("Client type '{$type}' not found");
        }

        $template = $templates[$type];

        // Generate random values based on template ranges
        $age = random_int($template['age_range'][0], $template['age_range'][1]);
        $income = random_int($template['income_range'][0], $template['income_range'][1]);
        
        // Expenses should be less than income
        $maxExpenses = min($template['expenses_range'][1], (int)($income * 0.9));
        $minExpenses = min($template['expenses_range'][0], $maxExpenses);
        $expenses = random_int($minExpenses, $maxExpenses);

        // Random credit history from options
        $creditHistoryOptions = $template['credit_history_options'];
        $creditHistory = $creditHistoryOptions[array_rand($creditHistoryOptions)];

        // Has deposit based on probability
        $hasDeposit = (mt_rand() / mt_getrandmax()) < $template['has_deposit_probability'];

        return [
            'type' => $type,
            'model_path' => $template['model_path'],
            'name' => $this->generateRandomName(),
            'age' => $age,
            'income' => $income,
            'expenses' => $expenses,
            'credit_history' => $creditHistory,
            'has_deposit' => $hasDeposit,
        ];
    }

    /**
     * Get list of available client types
     *
     * @return array<string> Array of available client type names
     */
    public function getAvailableTypes(): array
    {
        $templates = config('simulators.bank_simulator.client_templates', []);

        return array_keys($templates);
    }

    /**
     * Get template for a specific client type
     *
     * @param string $type Client type
     * @return array<string, mixed> Template array
     * @throws \InvalidArgumentException If type not found
     */
    public function getTemplate(string $type): array
    {
        $templates = config('simulators.bank_simulator.client_templates', []);

        if (!isset($templates[$type])) {
            throw new \InvalidArgumentException("Client type '{$type}' not found");
        }

        return $templates[$type];
    }

    /**
     * Generate random Russian name
     *
     * @return string Random name in format "Фамилия И.О."
     */
    public function generateRandomName(): string
    {
        $lastNames = [
            'Иванов', 'Петров', 'Сидоров', 'Смирнов', 'Кузнецов',
            'Попов', 'Соколов', 'Лебедев', 'Новиков', 'Морозов',
            'Волков', 'Алексеев', 'Лебедев', 'Семенов', 'Егоров',
            'Павлов', 'Козлов', 'Степанов', 'Николаев', 'Орлов',
        ];

        $firstNames = [
            'Александр', 'Дмитрий', 'Максим', 'Сергей', 'Андрей',
            'Алексей', 'Артем', 'Илья', 'Кирилл', 'Михаил',
            'Никита', 'Матвей', 'Роман', 'Владимир', 'Иван',
        ];

        $middleNames = [
            'Александрович', 'Дмитриевич', 'Максимович', 'Сергеевич', 'Андреевич',
            'Алексеевич', 'Артемович', 'Ильич', 'Кириллович', 'Михайлович',
            'Никитич', 'Матвеевич', 'Романович', 'Владимирович', 'Иванович',
        ];

        $lastName = $lastNames[array_rand($lastNames)];
        $firstName = $firstNames[array_rand($firstNames)];
        $middleName = $middleNames[array_rand($middleNames)];

        // Format: "Иванов А.П."
        $firstInitial = mb_substr($firstName, 0, 1);
        $middleInitial = mb_substr($middleName, 0, 1);

        return "{$lastName} {$firstInitial}.{$middleInitial}.";
    }
}
