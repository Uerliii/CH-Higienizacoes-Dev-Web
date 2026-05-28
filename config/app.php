<?php

/**
 * Configurações globais da aplicação CH Higienizações
 */

// ── Ambiente ───────────────────────────────────────────────────────────────────
define('APP_ENV',     getenv('APP_ENV')  ?: 'production');
define('APP_DEBUG',   getenv('APP_DEBUG') === 'true');
define('APP_URL',     getenv('APP_URL')  ?: 'http://localhost');
define('APP_NAME',    'CH Higienizações');

// ── Empresa ────────────────────────────────────────────────────────────────────
define('COMPANY_NAME',      'CH Higienizações');
define('COMPANY_FULL_NAME', 'Charles Higienizações');
define('COMPANY_PHONE',     '(75) 99707-6838');
define('COMPANY_WHATSAPP',  '5575997076838');
define('COMPANY_EMAIL',     'contato@chhigienizacoes.com.br');
define('COMPANY_CITY',      'Esplanada - BA');
define('COMPANY_INSTAGRAM', 'https://www.instagram.com/charleshigienizacoes?igsh=MWU2bmU2Z3gzeDZndw==');

// As configurações de IA (OpenRouter e Gemini) foram migradas 100% para o Banco de Dados (tabela `settings`).
// Nenhuma chave de API ou parâmetro sensível está mais salvo neste arquivo.

// ── Banco de dados ─────────────────────────────────────────────────────────────
define('DB_HOST',     getenv('DB_HOST')     ?: '127.0.0.1');
define('DB_PORT',     getenv('DB_PORT')     ?: '3306');
define('DB_DATABASE', getenv('DB_DATABASE') ?: 'ch_higienizacoes');
define('DB_USERNAME', getenv('DB_USERNAME') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_CHARSET',  'utf8mb4');

// ── Sessão ─────────────────────────────────────────────────────────────────────
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_secure',  APP_ENV === 'production' ? '1' : '0');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');

// ── Erros ──────────────────────────────────────────────────────────────────────
if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}
