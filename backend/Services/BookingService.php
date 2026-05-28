<?php

namespace Services;

use Models\Booking;

/**
 * BookingService — Camada de negócio para agendamentos.
 * Orquestra a validação, persistência e notificação.
 */
class BookingService
{
    private Booking $model;

    public function __construct()
    {
        $this->model = new Booking();
    }

    /**
     * Processa um novo agendamento completo (3 passos do wizard).
     * Retorna o ID criado ou lança ValidationException.
     */
    public function processBooking(array $rawData): array
    {
        // Sanitiza todos os campos
        $data = [
            'service'  => sanitize($rawData['service']  ?? ''),
            'data'     => sanitize($rawData['data']      ?? ''),
            'horario'  => sanitize($rawData['horario']   ?? ''),
            'nome'     => sanitize($rawData['nome']      ?? ''),
            'whatsapp' => preg_replace('/\D/', '', $rawData['whatsapp'] ?? ''),
            'cidade'   => sanitize($rawData['cidade']    ?? ''),
        ];

        // Valida
        $errors = $this->model->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Persiste no banco
        try {
            $id = $this->model->create($data);
            appLog('info', 'Agendamento criado', ['id' => $id, 'service' => $data['service']]);

            // Monta link do WhatsApp para redirecionar após confirmação
            $waMessage = buildWhatsAppMessage($data);
            $waLink    = 'https://wa.me/' . COMPANY_WHATSAPP . '?text=' . $waMessage;

            return [
                'success'    => true,
                'id'         => $id,
                'whatsapp_link' => $waLink,
                'message'    => 'Agendamento confirmado! Nossa equipe entrará em contato pelo WhatsApp em instantes.',
            ];
        } catch (\Throwable $e) {
            appLog('error', 'Erro ao criar agendamento', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'errors'  => ['geral' => 'Erro interno. Tente novamente.'],
            ];
        }
    }
}
