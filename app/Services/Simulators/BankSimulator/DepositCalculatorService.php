<?php

declare(strict_types=1);

namespace App\Services\Simulators\BankSimulator;

use InvalidArgumentException;

class DepositCalculatorService
{
    /**
     * Calculate deposit with capitalization (compound interest)
     *
     * Formula: Result = Initial * (1 + rate/periods)^(periods*years)
     * Where rate = annualRate / periods / 100
     *
     * @param  float  $initialAmount  Initial deposit amount
     * @param  float  $annualRate  Annual interest rate in percent (e.g., 8.0 for 8%)
     * @param  int  $years  Deposit term in years
     * @param  int  $capitalizationPeriods  Number of capitalization periods per year (default: 12 for monthly)
     * @return float Final deposit amount after capitalization
     *
     * @throws InvalidArgumentException If parameters are invalid
     */
    public function calculateDeposit(
        float $initialAmount,
        float $annualRate,
        int $years,
        int $capitalizationPeriods = 12
    ): float {
        if ($initialAmount <= 0) {
            throw new InvalidArgumentException('Initial amount must be greater than 0');
        }
        if ($annualRate < 0) {
            throw new InvalidArgumentException('Interest rate cannot be negative');
        }
        if ($years <= 0) {
            throw new InvalidArgumentException('Deposit term must be greater than 0');
        }
        if ($capitalizationPeriods <= 0) {
            throw new InvalidArgumentException('Capitalization periods must be greater than 0');
        }

        $ratePerPeriod = $annualRate / $capitalizationPeriods / 100;
        $totalPeriods = $capitalizationPeriods * $years;

        return $initialAmount * pow(1 + $ratePerPeriod, $totalPeriods);
    }

    /**
     * Calculate simple deposit without capitalization (simple interest)
     *
     * Formula: Result = Initial * (1 + rate * years)
     * Where rate = annualRate / 100
     *
     * @param  float  $initialAmount  Initial deposit amount
     * @param  float  $annualRate  Annual interest rate in percent (e.g., 8.0 for 8%)
     * @param  int  $years  Deposit term in years
     * @return float Final deposit amount
     *
     * @throws InvalidArgumentException If parameters are invalid
     */
    public function calculateSimpleDeposit(float $initialAmount, float $annualRate, int $years): float
    {
        if ($initialAmount <= 0) {
            throw new InvalidArgumentException('Initial amount must be greater than 0');
        }
        if ($annualRate < 0) {
            throw new InvalidArgumentException('Interest rate cannot be negative');
        }
        if ($years <= 0) {
            throw new InvalidArgumentException('Deposit term must be greater than 0');
        }

        $rate = $annualRate / 100;

        return $initialAmount * (1 + $rate * $years);
    }
}
