<?php

/**
 * Roteador simples — equivalente às rotas Express do sistema original.
 * Suporta GET, POST e parâmetros de URL.
 */

$requestUri    = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Normaliza o path removendo o prefixo da subpasta se existir
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($basePath && str_starts_with($requestUri, $basePath)) {
    $requestUri = substr($requestUri, strlen($basePath));
}
$requestUri = '/' . ltrim($requestUri, '/');

// ── Rotas ──────────────────────────────────────────────────────────────────────

// GET / — Página principal (equivalente ao Express static + index.html)
route('GET', '/', function () {
    $controller = new Controllers\HomeController();
    $controller->index();
});

// POST /api/chat — Endpoint do chatbot IA (equivalente ao POST /api/chat do server.js)
route('POST', '/api/chat', function () {
    $controller = new Controllers\ChatController();
    $controller->send();
});

// POST /api/booking — Salvar agendamento no banco
route('POST', '/api/booking', function () {
    $controller = new Controllers\BookingController();
    $controller->store();
});

// GET /api/booking/{id} — Consultar agendamento
route('GET', '/api/booking/(\d+)', function (string $id) {
    $controller = new Controllers\BookingController();
    $controller->show((int) $id);
});

// 404 fallback
http_response_code(404);
echo json_encode(['error' => 'Rota não encontrada.']);
exit;

// ── Router Helper ──────────────────────────────────────────────────────────────

function route(string $method, string $pattern, callable $handler): void
{
    global $requestUri, $requestMethod;

    $regex = '#^' . $pattern . '$#';

    if (
        strtoupper($method) === strtoupper($requestMethod)
        && preg_match($regex, $requestUri, $matches)
    ) {
        array_shift($matches); // remove o match completo
        call_user_func_array($handler, $matches);
        exit;
    }
}
