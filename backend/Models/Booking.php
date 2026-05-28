<?php

namespace Models;

use PDO;

/**
 * Booking Model — Representa um agendamento no banco de dados.
 * Equivalente ao schema de booking do sistema original.
 */
class Booking
{
    private PDO $db;

    // Serviços válidos (mesmos do HTML original)
    public const VALID_SERVICES = ['sofa', 'colchao', 'carro', 'tapete'];
    public const VALID_HORARIOS = ['manha', 'tarde'];

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Cria um novo agendamento (equivalente ao formulário wizard de 3 passos).
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO bookings
                (service, data_agendamento, horario, nome, whatsapp, cidade, created_at)
            VALUES
                (:service, :data, :horario, :nome, :whatsapp, :cidade, CURRENT_TIMESTAMP)
        ");

        $stmt->execute([
            ':service'  => $data['service'],
            ':data'     => $data['data'],
            ':horario'  => $data['horario'],
            ':nome'     => $data['nome'],
            ':whatsapp' => $data['whatsapp'],
            ':cidade'   => $data['cidade'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Busca agendamento por ID.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM bookings WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Lista todos os agendamentos (para painel admin futuro).
     */
    public function all(int $limit = 50): array
    {
        $stmt = $this->db->prepare("SELECT * FROM bookings ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Validação dos dados do formulário (3 steps do wizard original).
     */
    public function validate(array $data): array
    {
        $errors = [];

        // Step 1: Serviço
        if (empty($data['service']) || !in_array($data['service'], self::VALID_SERVICES, true)) {
            $errors['service'] = 'Selecione um serviço válido.';
        }

        // Step 2: Data
        if (empty($data['data'])) {
            $errors['data'] = 'Informe uma data válida.';
        } else {
            $date = \DateTime::createFromFormat('Y-m-d', $data['data']);
            if (!$date || $date < new \DateTime('today')) {
                $errors['data'] = 'A data deve ser hoje ou futura.';
            }
        }

        // Step 2: Horário
        if (empty($data['horario']) || !in_array($data['horario'], self::VALID_HORARIOS, true)) {
            $errors['horario'] = 'Escolha um horário preferencial.';
        }

        // Step 3: Nome
        if (empty(trim($data['nome'] ?? ''))) {
            $errors['nome'] = 'Informe seu nome completo.';
        }

        // Step 3: WhatsApp
        $whatsapp = preg_replace('/\D/', '', $data['whatsapp'] ?? '');
        if (strlen($whatsapp) < 10) {
            $errors['whatsapp'] = 'Informe um WhatsApp válido.';
        }

        // Step 3: Cidade
        if (empty(trim($data['cidade'] ?? ''))) {
            $errors['cidade'] = 'Informe sua cidade ou bairro.';
        }

        return $errors;
    }
}
