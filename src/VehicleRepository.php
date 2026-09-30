<?php

declare(strict_types=1);

namespace App;

use PDO;
use RuntimeException;

final class VehicleRepository
{
    public function __construct(private PDO $connection)
    {
    }

    public function findAll(array $filters = []): array
    {
        $sql = 'SELECT
                    v.id,
                    v.model_name,
                    v.type_id,
                    vt.name AS vehicle_type,
                    v.doors,
                    v.transmission,
                    v.fuel,
                    v.price
                FROM vehicles v
                INNER JOIN vehicle_types vt ON vt.id = v.type_id';

        $conditions = [];
        $parameters = [];

        if (isset($filters['price_min'])) {
            $conditions[] = 'v.price >= :price_min';
            $parameters['price_min'] = $filters['price_min'];
        }

        if (isset($filters['price_max'])) {
            $conditions[] = 'v.price <= :price_max';
            $parameters['price_max'] = $filters['price_max'];
        }

        if (isset($filters['transmission'])) {
            $conditions[] = 'v.transmission = :transmission';
            $parameters['transmission'] = $filters['transmission'];
        }

        if (isset($filters['type_id'])) {
            $conditions[] = 'v.type_id = :type_id';
            $parameters['type_id'] = $filters['type_id'];
        }

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sortOptions = [
            'name_asc' => 'v.model_name ASC',
            'name_desc' => 'v.model_name DESC',
            'price_asc' => 'v.price ASC',
            'price_desc' => 'v.price DESC',
        ];

        $sql .= ' ORDER BY ' . ($sortOptions[$filters['sort'] ?? ''] ?? 'v.id ASC');

        $statement = $this->connection->prepare($sql);
        $statement->execute($parameters);

        $rows = $statement->fetchAll();
        $vehicles = [];

        foreach ($rows as $row) {
            $vehicle = $this->mapVehicle($row);
            $vehicles[] = $vehicle;
        }

        return $vehicles;
    }

    public function findById(int $id): ?Vehicle
    {
        $statement = $this->connection->prepare(
            'SELECT
                v.id,
                v.model_name,
                v.type_id,
                vt.name AS vehicle_type,
                v.doors,
                v.transmission,
                v.fuel,
                v.price
             FROM vehicles v
             INNER JOIN vehicle_types vt ON vt.id = v.type_id
             WHERE v.id = :id'
        );

        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $this->mapVehicle($row);
    }

    public function create(Vehicle $vehicle): Vehicle
    {
        $statement = $this->connection->prepare(
            'INSERT INTO vehicles
                (model_name, type_id, doors, transmission, fuel, price)
             VALUES
                (:model_name, :type_id, :doors, :transmission, :fuel, :price)'
        );

        $statement->execute($this->vehicleParameters($vehicle));

        $createdVehicle = $this->findById(
            (int) $this->connection->lastInsertId()
        );

        if ($createdVehicle === null) {
            throw new RuntimeException('The created vehicle could not be loaded.');
        }

        return $createdVehicle;
    }

    public function update(int $id, Vehicle $vehicle): ?Vehicle
    {
        $statement = $this->connection->prepare(
            'UPDATE vehicles
             SET model_name = :model_name,
                 type_id = :type_id,
                 doors = :doors,
                 transmission = :transmission,
                 fuel = :fuel,
                 price = :price
             WHERE id = :id'
        );

        $parameters = $this->vehicleParameters($vehicle);
        $parameters['id'] = $id;
        $statement->execute($parameters);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $statement = $this->connection->prepare(
            'DELETE FROM vehicles WHERE id = :id'
        );
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }

    private function vehicleParameters(Vehicle $vehicle): array
    {
        return [
            'model_name' => $vehicle->modelName,
            'type_id' => $vehicle->typeId,
            'doors' => $vehicle->doors,
            'transmission' => $vehicle->transmission,
            'fuel' => $vehicle->fuel,
            'price' => $vehicle->price,
        ];
    }

    private function mapVehicle(array $row): Vehicle
    {
        return new Vehicle(
            id: (int) $row['id'],
            modelName: (string) $row['model_name'],
            typeId: (int) $row['type_id'],
            vehicleType: (string) $row['vehicle_type'],
            doors: (int) $row['doors'],
            transmission: (string) $row['transmission'],
            fuel: (string) $row['fuel'],
            price: (float) $row['price'],
        );
    }
}
