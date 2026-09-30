<?php

declare(strict_types=1);

namespace App;

use PDOException;

final class VehicleController
{
    public function __construct(private VehicleRepository $repository)
    {
    }

    public function getAll(array $filters): void
    {
        $errors = VehicleValidator::validateFilters($filters);

        if ($errors !== []) {
            $this->validationError($errors);
            return;
        }

        $vehicles = $this->repository->findAll($filters);
        $response = [];

        foreach ($vehicles as $vehicle) {
            $response[] = $this->vehicleToArray($vehicle);
        }

        $this->respond($response);
    }

    public function create(array $data): void
    {
        $errors = VehicleValidator::validate($data);

        if ($errors !== []) {
            $this->validationError($errors);
            return;
        }

        try {
            $vehicle = $this->repository->create(
                $this->vehicleFromData($data)
            );
        } catch (PDOException $exception) {
            if ((string) $exception->getCode() === '23000') {
                $this->validationError([
                    'type_id' => 'The selected vehicle type does not exist.',
                ]);
                return;
            }

            throw $exception;
        }

        $this->respond($this->vehicleToArray($vehicle), 201);
    }

    public function update(int $id, array $data): void
    {
        $errors = VehicleValidator::validate($data);

        if ($errors !== []) {
            $this->validationError($errors);
            return;
        }

        try {
            $vehicle = $this->repository->update(
                $id,
                $this->vehicleFromData($data, $id)
            );
        } catch (PDOException $exception) {
            if ((string) $exception->getCode() === '23000') {
                $this->validationError([
                    'type_id' => 'The selected vehicle type does not exist.',
                ]);
                return;
            }

            throw $exception;
        }

        if ($vehicle === null) {
            $this->respond(['error' => 'Vehicle not found.'], 404);
            return;
        }

        $this->respond($this->vehicleToArray($vehicle));
    }

    public function delete(int $id): void
    {
        if (!$this->repository->delete($id)) {
            $this->respond(['error' => 'Vehicle not found.'], 404);
            return;
        }

        http_response_code(204);
    }

    private function vehicleFromData(array $data, ?int $id = null): Vehicle
    {
        return new Vehicle(
            id: $id,
            modelName: trim($data['model_name']),
            typeId: $data['type_id'],
            vehicleType: null,
            doors: $data['doors'],
            transmission: $data['transmission'],
            fuel: $data['fuel'],
            price: (float) $data['price'],
        );
    }

    private function vehicleToArray(Vehicle $vehicle): array
    {
        return [
            'id' => $vehicle->id,
            'model_name' => $vehicle->modelName,
            'type_id' => $vehicle->typeId,
            'vehicle_type' => $vehicle->vehicleType,
            'doors' => $vehicle->doors,
            'transmission' => $vehicle->transmission,
            'fuel' => $vehicle->fuel,
            'price' => $vehicle->price,
        ];
    }

    private function validationError(array $errors): void
    {
        $this->respond([
            'error' => 'Validation failed.',
            'fields' => $errors,
        ], 422);
    }

    private function respond(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            $data,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }
}
