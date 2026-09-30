<?php

declare(strict_types=1);

use App\VehicleValidator;
use PHPUnit\Framework\TestCase;

final class VehicleValidatorTest extends TestCase
{
    public function testValidVehicleHasNoErrors(): void
    {
        $errors = VehicleValidator::validate($this->validVehicle());

        self::assertSame([], $errors);
    }

    public function testMissingFieldsAreRejected(): void
    {
        $errors = VehicleValidator::validate([]);

        self::assertSame(
            ['model_name', 'type_id', 'doors', 'price', 'transmission', 'fuel'],
            array_keys($errors),
        );
    }

    public function testDatabaseNumericLimitsAreEnforced(): void
    {
        $vehicle = $this->validVehicle();
        $vehicle['doors'] = 256;
        $vehicle['price'] = 100000000;

        $errors = VehicleValidator::validate($vehicle);

        self::assertArrayHasKey('doors', $errors);
        self::assertArrayHasKey('price', $errors);
    }

    public function testUnicodeModelNameUsesCharacterLength(): void
    {
        $vehicle = $this->validVehicle();
        $vehicle['model_name'] = str_repeat('α', 61);

        $errors = VehicleValidator::validate($vehicle);

        self::assertArrayNotHasKey('model_name', $errors);
    }

    public function testValidFiltersHaveNoErrors(): void
    {
        $errors = VehicleValidator::validateFilters([
            'price_min' => '100',
            'price_max' => '200',
            'type_id' => '1',
            'transmission' => 'automatic',
            'sort' => 'price_asc',
        ]);

        self::assertSame([], $errors);
    }

    public function testInvalidFiltersAreRejected(): void
    {
        $errors = VehicleValidator::validateFilters([
            'price_min' => ['100'],
            'type_id' => 'abc',
            'transmission' => 'CVT',
            'sort' => ['price_asc'],
        ]);

        self::assertSame(
            ['price_min', 'type_id', 'transmission', 'sort'],
            array_keys($errors),
        );
    }

    public function testInvalidPriceRangeIsRejected(): void
    {
        $errors = VehicleValidator::validateFilters([
            'price_min' => '200',
            'price_max' => '100',
        ]);

        self::assertArrayHasKey('price_max', $errors);
    }

    private function validVehicle(): array
    {
        return [
            'model_name' => 'Fiat Panda',
            'type_id' => 1,
            'doors' => 5,
            'transmission' => 'manual',
            'fuel' => 'petrol',
            'price' => 90,
        ];
    }
}
