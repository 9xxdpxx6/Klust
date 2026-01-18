<?php

declare(strict_types=1);

namespace App\Services\Simulators\BankSimulator;

class ClientGeneratorService
{
    /**
     * Generate client data (stub implementation)
     *
     * This is a temporary stub that returns static data.
     * Full implementation will be added in module 08.
     *
     * @param string $type Client type ('random', 'student', 'family', etc.)
     * @return array<string, mixed> Client data array
     */
    public function generateClient(string $type = 'random'): array
    {
        // Stub: return static data for now
        // Full implementation will be in module 08
        $templates = config('simulators.bank_simulator.client_templates', []);

        if ($type !== 'random' && isset($templates[$type])) {
            return $templates[$type];
        }

        // Default static data
        return [
            'type' => 'student',
            'name' => 'Иванов А.П.',
            'age' => 25,
            'income' => 80000,
            'expenses' => 60000,
            'credit_history' => 'good',
            'has_deposit' => false,
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
}
