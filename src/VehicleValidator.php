<?php

declare(strict_types=1);

namespace App;

final class VehicleValidator
{
    private const TRANSMISSIONS = ['manual', 'automatic'];
    private const FUELS = ['petrol', 'diesel', 'hybrid', 'electric'];
    private const SORT_OPTIONS = [
        'name_asc',
        'name_desc',
        'price_asc',
        'price_desc',
    ];
    private const MAX_TYPE_ID = 255;
    private const MAX_DOORS = 255;
    private const MAX_PRICE = 99999999.99;

    public static function validate(array $data): array
    {
        $errors = [];

        if (
            !isset($data['model_name'])
            || !is_string($data['model_name'])
            || trim($data['model_name']) === ''
        ) {
            $errors['model_name'] = 'Model name is required.';
        } elseif (mb_strlen(trim($data['model_name'])) > 120) {
            $errors['model_name'] = 'Model name must not exceed 120 characters.';
        }

        if (
            !isset($data['type_id'])
            || !is_int($data['type_id'])
            || $data['type_id'] <= 0
            || $data['type_id'] > self::MAX_TYPE_ID
        ) {
            $errors['type_id'] = 'Type ID must be an integer between 1 and 255.';
        }

        if (
            !isset($data['doors'])
            || !is_int($data['doors'])
            || $data['doors'] <= 0
            || $data['doors'] > self::MAX_DOORS
        ) {
            $errors['doors'] = 'Doors must be an integer between 1 and 255.';
        }

        if (
            !isset($data['price'])
            || (!is_int($data['price']) && !is_float($data['price']))
            || $data['price'] < 0
            || $data['price'] > self::MAX_PRICE
            || !is_finite((float) $data['price'])
        ) {
            $errors['price'] = 'Price must be between 0 and 99999999.99.';
        }

        if (
            !isset($data['transmission'])
            || !is_string($data['transmission'])
            || !in_array($data['transmission'], self::TRANSMISSIONS, true)
        ) {
            $errors['transmission'] = 'Transmission must be manual or automatic.';
        }

        if (
            !isset($data['fuel'])
            || !is_string($data['fuel'])
            || !in_array($data['fuel'], self::FUELS, true)
        ) {
            $errors['fuel'] = 'Fuel must be petrol, diesel, hybrid, or electric.';
        }

        return $errors;
    }

    public static function validateFilters(array $filters): array
    {
        $errors = [];

        foreach (['price_min', 'price_max'] as $field) {
            if (!array_key_exists($field, $filters)) {
                continue;
            }

            $value = $filters[$field];

            if (
                !is_string($value)
                || $value === ''
                || !is_numeric($value)
                || (float) $value < 0
                || (float) $value > self::MAX_PRICE
                || !is_finite((float) $value)
            ) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field))
                    . ' must be between 0 and 99999999.99.';
            }
        }

        if (
            !isset($errors['price_min'])
            && !isset($errors['price_max'])
            && isset($filters['price_min'], $filters['price_max'])
            && (float) $filters['price_min'] > (float) $filters['price_max']
        ) {
            $errors['price_max'] = 'Price max must be greater than or equal to price min.';
        }

        if (array_key_exists('type_id', $filters)) {
            $typeId = $filters['type_id'];
            $validTypeId = is_string($typeId)
                && filter_var($typeId, FILTER_VALIDATE_INT) !== false
                && (int) $typeId >= 1
                && (int) $typeId <= self::MAX_TYPE_ID;

            if (!$validTypeId) {
                $errors['type_id'] = 'Type ID must be an integer between 1 and 255.';
            }
        }

        if (
            array_key_exists('transmission', $filters)
            && (
                !is_string($filters['transmission'])
                || !in_array($filters['transmission'], self::TRANSMISSIONS, true)
            )
        ) {
            $errors['transmission'] = 'Transmission must be manual or automatic.';
        }

        if (
            array_key_exists('sort', $filters)
            && (
                !is_string($filters['sort'])
                || !in_array($filters['sort'], self::SORT_OPTIONS, true)
            )
        ) {
            $errors['sort'] = 'Sort must be name_asc, name_desc, price_asc, or price_desc.';
        }

        return $errors;
    }
}
