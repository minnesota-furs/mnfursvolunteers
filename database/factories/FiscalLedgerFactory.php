<?php

namespace Database\Factories;

use App\Models\FiscalLedger;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FiscalLedger>
 */
class FiscalLedgerFactory extends Factory
{
    protected $model = FiscalLedger::class;

    public function definition(): array
    {
        $year = fake()->numberBetween(2020, 2026);

        return [
            'name'       => "Fiscal Year {$year}",
            'start_date' => "{$year}-01-01",
            'end_date'   => "{$year}-12-31",
        ];
    }

    public function forYear(int $year): static
    {
        return $this->state(fn (array $attributes) => [
            'name'       => "Fiscal Year {$year}",
            'start_date' => "{$year}-01-01",
            'end_date'   => "{$year}-12-31",
        ]);
    }
}
