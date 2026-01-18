<?php

declare(strict_types=1);

namespace App\Services\Simulators\BankSimulator;

use InvalidArgumentException;

class CreditCalculatorService
{
    /**
     * Calculate annuity (equal) monthly payment
     *
     * Formula: Payment = Amount * (rate * (1+rate)^months) / ((1+rate)^months - 1)
     * Where rate = annualRate / 12 / 100
     *
     * @param float $amount Loan amount
     * @param int $months Loan term in months
     * @param float $annualRate Annual interest rate in percent (e.g., 15.0 for 15%)
     * @return float Monthly payment amount
     * @throws InvalidArgumentException If parameters are invalid
     */
    public function calculateAnnuityPayment(float $amount, int $months, float $annualRate): float
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Loan amount must be greater than 0');
        }
        if ($months <= 0) {
            throw new InvalidArgumentException('Loan term must be greater than 0');
        }
        if ($annualRate < 0) {
            throw new InvalidArgumentException('Interest rate cannot be negative');
        }

        $monthlyRate = $annualRate / 12 / 100;

        // Handle zero interest rate case
        if ($monthlyRate == 0) {
            return $amount / $months;
        }

        $numerator = $monthlyRate * pow(1 + $monthlyRate, $months);
        $denominator = pow(1 + $monthlyRate, $months) - 1;

        return $amount * ($numerator / $denominator);
    }

    /**
     * Calculate total payment amount over loan term
     *
     * @param float $monthlyPayment Monthly payment amount
     * @param int $months Loan term in months
     * @return float Total payment amount
     * @throws InvalidArgumentException If parameters are invalid
     */
    public function calculateTotalPayment(float $monthlyPayment, int $months): float
    {
        if ($monthlyPayment <= 0) {
            throw new InvalidArgumentException('Monthly payment must be greater than 0');
        }
        if ($months <= 0) {
            throw new InvalidArgumentException('Loan term must be greater than 0');
        }

        return $monthlyPayment * $months;
    }

    /**
     * Calculate overpayment (total interest paid)
     *
     * @param float $totalPayment Total payment amount
     * @param float $amount Original loan amount
     * @return float Overpayment amount
     * @throws InvalidArgumentException If parameters are invalid
     */
    public function calculateOverpayment(float $totalPayment, float $amount): float
    {
        if ($totalPayment < 0) {
            throw new InvalidArgumentException('Total payment cannot be negative');
        }
        if ($amount <= 0) {
            throw new InvalidArgumentException('Loan amount must be greater than 0');
        }
        if ($totalPayment < $amount) {
            throw new InvalidArgumentException('Total payment cannot be less than loan amount');
        }

        return $totalPayment - $amount;
    }
}
