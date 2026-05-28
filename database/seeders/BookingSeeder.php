<?php

/**
 * BookingSeeder — Popula o banco com dados de exemplo para testes.
 * Execute: php database/seeders/BookingSeeder.php
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../backend/helpers.php';
require_once __DIR__ . '/../../backend/Models/Database.php';

use Models\Database;

$db = Database::connection();

$seeds = [
    ['sofa',    '2026-06-05', 'manha', 'Maria Santos',   '75991234567', 'Esplanada - BA'],
    ['colchao', '2026-06-07', 'tarde', 'João Pereira',   '75998765432', 'Alagoinhas - BA'],
    ['carro',   '2026-06-10', 'manha', 'Ana Oliveira',   '75997001122', 'Esplanada - BA'],
    ['tapete',  '2026-06-12', 'tarde', 'Carlos Souza',   '75993344556', 'Ribeira do Amparo - BA'],
    ['sofa',    '2026-06-15', 'manha', 'Patrícia Lima',  '75995566778', 'Entre Rios - BA'],
];

$stmt = $db->prepare("
    INSERT INTO bookings (service, data_agendamento, horario, nome, whatsapp, cidade, created_at)
    VALUES (:service, :data, :horario, :nome, :whatsapp, :cidade, NOW())
");

foreach ($seeds as [$service, $data, $horario, $nome, $whatsapp, $cidade]) {
    $stmt->execute([
        ':service'  => $service,
        ':data'     => $data,
        ':horario'  => $horario,
        ':nome'     => $nome,
        ':whatsapp' => $whatsapp,
        ':cidade'   => $cidade,
    ]);
    echo "✅ Inserido: {$nome} — {$service} em {$data}\n";
}

echo "\n🎉 Seeder concluído! " . count($seeds) . " agendamentos inseridos.\n";
