<?php

declare(strict_types=1);

namespace App;

final class Vehicle
{
    public function __construct(
        public ?int $id,
        public string $modelName,
        public int $typeId,
        public ?string $vehicleType,
        public int $doors,
        public string $transmission,
        public string $fuel,
        public float $price,
    ) {
    }
}
