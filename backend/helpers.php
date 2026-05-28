<?php

/**
 * Funções helper globais da aplicação.
 */

// ── View Renderer ──────────────────────────────────────────────────────────────

/**
 * Renderiza uma view com variáveis extraídas.
 * Equivalente ao template engine do Express/Blade.
 */
function view(string $name, array $data = []): void
{
    $file = VIEWS_PATH . '/' . str_replace('.', '/', $name) . '.php';
    if (!file_exists($file)) {
        http_response_code(500);
        die("View não encontrada: {$name}");
    }
    extract($data, EXTR_SKIP);
    require $file;
}

// ── JSON Response ──────────────────────────────────────────────────────────────

function jsonResponse(mixed $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ── CSRF ───────────────────────────────────────────────────────────────────────

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="_token" value="' . htmlspecialchars(csrfToken()) . '">';
}

function verifyCsrf(): void
{
    $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals(csrfToken(), $token)) {
        jsonResponse(['error' => 'Token CSRF inválido.'], 419);
    }
}

// ── Sanitize ───────────────────────────────────────────────────────────────────

function sanitize(string $value): string
{
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

// ── Asset URL ─────────────────────────────────────────────────────────────────

function asset(string $path): string
{
    $base = rtrim(APP_URL, '/');
    return $base . '/' . ltrim($path, '/');
}

// ── Log ───────────────────────────────────────────────────────────────────────

function appLog(string $level, string $message, array $context = []): void
{
    try {
        require_once dirname(__DIR__) . '/backend/Models/Database.php';
        $pdo = \Models\Database::connection();
        $stmt = $pdo->prepare("INSERT INTO system_logs (level, message, context) VALUES (:level, :message, :context)");
        $stmt->execute([
            ':level'   => strtoupper($level),
            ':message' => $message,
            ':context' => !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : null
        ]);
    } catch (\Throwable $e) {
        // Fallback silencioso se o banco estiver indisponível no momento de log
    }
}

// ── WhatsApp message builder ───────────────────────────────────────────────────

function buildWhatsAppMessage(array $booking): string
{
    $service  = match($booking['service']) {
        'sofa'    => 'Sofá',
        'colchao' => 'Colchão',
        'carro'   => 'Carro (Bancos)',
        'tapete'  => 'Tapete',
        default   => ucfirst($booking['service']),
    };
    $horario  = $booking['horario'] === 'manha' ? 'Manhã (08h-12h)' : 'Tarde (13h-18h)';
    $msg      = "Olá! Acabei de agendar pelo site.\n";
    $msg     .= "Serviço: {$service}\n";
    $msg     .= "Data: {$booking['data']}\n";
    $msg     .= "Horário: {$horario}\n";
    $msg     .= "Nome: {$booking['nome']}\n";
    $msg     .= "Cidade: {$booking['cidade']}";
    return urlencode($msg);
}
