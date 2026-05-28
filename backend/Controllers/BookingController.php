<?php

namespace Controllers;

use Services\BookingService;

/**
 * BookingController — Gerencia o agendamento via API REST.
 * Equivalente ao formulário de 3 passos que antes era puramente client-side.
 * Agora persiste no banco MySQL e retorna link WhatsApp.
 */
class BookingController
{
    private BookingService $service;

    public function __construct()
    {
        $this->service = new BookingService();
    }

    /**
     * POST /api/booking — Cria um agendamento.
     * Aceita JSON ou form-urlencoded.
     */
    public function store(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // CSRF (APIs internas)
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_token'] ?? '';
        if (!hash_equals(csrfToken(), $token)) {
            http_response_code(419);
            echo json_encode(['success' => false, 'message' => 'Token inválido.']);
            return;
        }

        // Suporta JSON body ou form POST
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw  = file_get_contents('php://input');
            $data = json_decode($raw, true) ?? [];
        } else {
            $data = $_POST;
        }

        $result = $this->service->processBooking($data);

        if ($result['success']) {
            http_response_code(201);
        } else {
            http_response_code(422);
        }

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /**
     * GET /api/booking/{id} — Retorna dados de um agendamento.
     */
    public function show(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $model   = new \Models\Booking();
        $booking = $model->find($id);

        if (!$booking) {
            http_response_code(404);
            echo json_encode(['error' => 'Agendamento não encontrado.']);
            return;
        }

        echo json_encode($booking, JSON_UNESCAPED_UNICODE);
    }
}
