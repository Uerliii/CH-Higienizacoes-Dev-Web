<?php

namespace Controllers;

use Services\OpenRouterService;

/**
 * ChatController — Equivalente exato ao endpoint POST /api/chat do server.js.
 *
 * Reproduz fielmente:
 * - Suporte a { history } (array) OU { message } (string) no body
 * - Resposta { reply: string }
 * - Erro 400 para payload vazio
 * - Erro 500 com mensagem amigável em fallback total
 */
class ChatController
{
    public function send(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Lê o body JSON (equivalente ao req.body do Express)
        $raw  = file_get_contents('php://input');
        $body = json_decode($raw, true) ?? [];

        // Reproduz a lógica exata do server.js:
        // if (history && Array.isArray(history) && history.length > 0) → usa history
        // else if (message) → cria [{role:'user', content:message}]
        // else → 400
        if (!empty($body['history']) && is_array($body['history']) && count($body['history']) > 0) {
            $history = $body['history'];
        } elseif (!empty($body['message'])) {
            $history = [['role' => 'user', 'content' => (string) $body['message']]];
        } else {
            http_response_code(400);
            echo json_encode(['reply' => 'Mensagem vazia.']);
            return;
        }

        // Sanitiza o histórico (segurança extra que o Node não tinha)
        $history = array_map(function (array $msg): array {
            return [
                'role'    => in_array($msg['role'] ?? '', ['user', 'assistant', 'system'], true) ? $msg['role'] : 'user',
                'content' => mb_substr(strip_tags((string) ($msg['content'] ?? '')), 0, 2000),
            ];
        }, $history);

        try {
            require_once dirname(__DIR__, 2) . '/backend/Models/Database.php';
            $pdo = \Models\Database::connection();
            
            // Verifica qual provedor de IA usar (padrão é openrouter)
            $stmt = $pdo->query("SELECT `value` FROM settings WHERE `key_name` = 'ai_provider'");
            $provider = $stmt->fetchColumn() ?: 'openrouter';

            if ($provider === 'gemini') {
                require_once dirname(__DIR__) . '/Services/GeminiService.php';
                $service = new \Services\GeminiService();
            } else {
                $service = new OpenRouterService();
            }

            $reply = $service->chat($history);

            // Gravar log no banco MySQL (Auditoria de Conversas)
            try {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $sessionId = session_id() ?: 'sess_' . md5($_SERVER['REMOTE_ADDR']);
                
                require_once dirname(__DIR__, 2) . '/backend/Models/Database.php';
                $pdo = \Models\Database::connection();
                
                // Pega apenas a última mensagem do usuário (já que o frontend manda o histórico todo)
                $lastUserMessage = end($history);
                if ($lastUserMessage && $lastUserMessage['role'] == 'user') {
                    $stmt = $pdo->prepare("INSERT INTO chat_logs (session_id, role, content) VALUES (?, ?, ?)");
                    $stmt->execute([$sessionId, 'user', $lastUserMessage['content']]);
                }
                
                // Grava a resposta da IA
                $stmt = $pdo->prepare("INSERT INTO chat_logs (session_id, role, content) VALUES (?, ?, ?)");
                $stmt->execute([$sessionId, 'assistant', $reply]);
                
            } catch (\Exception $e) {
                appLog('error', 'Erro ao salvar chat_log', ['message' => $e->getMessage()]);
            }

            http_response_code(200);
            echo json_encode(['reply' => $reply], JSON_UNESCAPED_UNICODE);

        } catch (\Throwable $e) {
            appLog('error', 'ChatController::send falhou', ['message' => $e->getMessage()]);
            http_response_code(500);
            echo json_encode([
                'reply' => 'Estou com dificuldades técnicas agora. Por favor, nos chame no WhatsApp! 💙',
            ]);
        }
    }
}
