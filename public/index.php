<?php

declare(strict_types=1);

use App\Database;
use App\VehicleController;
use App\VehicleRepository;

require_once __DIR__ . '/../vendor/autoload.php';

function sendJson(array $data, int $status): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(
        $data,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );

    exit;
}

function readJsonBody(): array
{
    $body = file_get_contents('php://input');

    if ($body === false || trim($body) === '') {
        sendJson(['error' => 'Request body is required.'], 400);
    }

    try {
        $data = json_decode($body, false, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        sendJson(['error' => 'Request body must contain valid JSON.'], 400);
    }

    if (!is_object($data)) {
        sendJson(['error' => 'JSON body must be an object.'], 400);
    }

    return (array) $data;
}

try {
    $connection = Database::connect();
    $repository = new VehicleRepository($connection);
    $controller = new VehicleController($repository);

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $path = is_string($path) ? rtrim($path, '/') : '/';
    $path = $path === '' ? '/' : $path;

    if ($path === '/vehicles') {
        if ($method === 'GET') {
            $controller->getAll($_GET);
            exit;
        }

        if ($method === 'POST') {
            $controller->create(readJsonBody());
            exit;
        }

        header('Allow: GET, POST');
        sendJson(['error' => 'Method not allowed.'], 405);
    }

    if (preg_match('#^/vehicles/([1-9][0-9]*)$#', $path, $matches) === 1) {
        $id = (int) $matches[1];

        if ($method === 'PUT') {
            $controller->update($id, readJsonBody());
            exit;
        }

        if ($method === 'DELETE') {
            $controller->delete($id);
            exit;
        }

        header('Allow: PUT, DELETE');
        sendJson(['error' => 'Method not allowed.'], 405);
    }

    sendJson(['error' => 'Endpoint not found.'], 404);
} catch (Throwable $exception) {
    error_log($exception->__toString());
    sendJson(['error' => 'Internal server error.'], 500);
}
