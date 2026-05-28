-- ============================================================
-- CH Higienizações — Migration: criar tabela de agendamentos
-- Equivalente ao schema implícito no formulário original
-- ============================================================

CREATE DATABASE IF NOT EXISTS `ch_higienizacoes`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `ch_higienizacoes`;

-- Tabela principal de agendamentos
CREATE TABLE IF NOT EXISTS `bookings` (
    `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `service`           ENUM('sofa','colchao','carro','tapete') NOT NULL
                            COMMENT 'Serviço escolhido no Step 1 do wizard',
    `data_agendamento`  DATE            NOT NULL
                            COMMENT 'Data escolhida no Step 2 do wizard',
    `horario`           ENUM('manha','tarde') NOT NULL
                            COMMENT 'Horário preferencial: manhã 08-12h / tarde 13-18h',
    `nome`              VARCHAR(150)    NOT NULL
                            COMMENT 'Nome completo (Step 3)',
    `whatsapp`          VARCHAR(20)     NOT NULL
                            COMMENT 'Número WhatsApp somente dígitos (Step 3)',
    `cidade`            VARCHAR(150)    NOT NULL
                            COMMENT 'Cidade / Bairro (Step 3)',
    `status`            ENUM('pendente','confirmado','concluido','cancelado')
                            NOT NULL DEFAULT 'pendente'
                            COMMENT 'Status do agendamento',
    `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                            ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_data`       (`data_agendamento`),
    INDEX `idx_service`    (`service`),
    INDEX `idx_status`     (`status`),
    INDEX `idx_whatsapp`   (`whatsapp`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Agendamentos de higienização — CH Higienizações';

-- Tabela de logs do chatbot (histórico de interações com a IA)
CREATE TABLE IF NOT EXISTS `chat_logs` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `session_id` VARCHAR(64)  NOT NULL COMMENT 'ID da sessão PHP',
    `role`       ENUM('user','assistant','system') NOT NULL,
    `content`    TEXT         NOT NULL,
    `model_used` VARCHAR(120) NULL     COMMENT 'Modelo OpenRouter que respondeu',
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_session` (`session_id`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Log de interações do chatbot Ana';

-- Tabela de auditoria (segurança)
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `action`     VARCHAR(100)  NOT NULL,
    `table_name` VARCHAR(60)   NULL,
    `record_id`  INT UNSIGNED  NULL,
    `payload`    JSON          NULL,
    `ip_address` VARCHAR(45)   NULL,
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_action`  (`action`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Log de auditoria do sistema';
