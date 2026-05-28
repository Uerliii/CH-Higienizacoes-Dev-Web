<?php

namespace Services;

/**
 * OpenRouterService — Equivalente EXATO ao server.js original.
 *
 * Reproduz:
 * - Array de modelos com fallback em cascata
 * - Timeout de 9 segundos por modelo
 * - Suporte a histórico de conversa (chatHistory)
 * - System prompt carregado do arquivo prompt.txt
 * - Markdown **negrito** preservado na resposta
 */
class OpenRouterService
{
    private array $models;
    private string $apiKey;
    private string $systemPrompt;
    private int $maxTokens;
    private float $temperature;
    private int $timeout;
    private string $apiUrl;

    public function __construct()
    {
        // Pega as chaves da tabela settings
        require_once dirname(__DIR__, 2) . '/backend/Models/Database.php';
        try {
            $pdo = \Models\Database::connection();
            $stmt = $pdo->query("SELECT `key_name`, `value` FROM settings WHERE `key_name` IN ('openrouter_api_key', 'chatbot_prompt', 'openrouter_models', 'openrouter_max_tokens', 'openrouter_temperature', 'openrouter_timeout')");
            $settings = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
        } catch (\Exception $e) {
            $settings = [];
        }

        $this->apiKey  = $settings['openrouter_api_key'] ?? '';
        $this->apiUrl  = 'https://openrouter.ai/api/v1/chat/completions';
        $this->maxTokens = isset($settings['openrouter_max_tokens']) ? (int)$settings['openrouter_max_tokens'] : 200;
        $this->temperature = isset($settings['openrouter_temperature']) ? (float)$settings['openrouter_temperature'] : 0.4;
        $this->timeout = isset($settings['openrouter_timeout']) ? (int)$settings['openrouter_timeout'] : 9;
        
        if (!empty($settings['openrouter_models'])) {
            $this->models = array_map('trim', explode(',', $settings['openrouter_models']));
        } else {
            $this->models = [
                'meta-llama/llama-3.2-3b-instruct:free',
                'google/gemma-3-4b-it:free',
                'liquid/lfm-2.5-1.2b-instruct:free',
                'meta-llama/llama-3.3-70b-instruct:free',
                'google/gemma-3-12b-it:free',
                'nousresearch/hermes-3-llama-3.1-405b:free'
            ];
        }
        
        if (!empty($settings['chatbot_prompt'])) {
            $this->systemPrompt = $settings['chatbot_prompt'];
        } else {
            $promptFile = BASE_PATH . '/config/prompt.txt';
            $this->systemPrompt = file_exists($promptFile) ? file_get_contents($promptFile) : $this->defaultPrompt();
        }
    }

    /**
     * Envia mensagem para a IA com fallback entre modelos.
     * Equivalente à função callOpenRouter() do server.js.
     *
     * @param  array $history  Histórico de mensagens [{role, content}, ...]
     * @return string          Resposta da IA
     */
    public function chat(array $history): string
    {
        if (empty($this->apiKey)) {
            appLog('error', 'Chave da API OpenRouter não configurada.');
            return 'A chave da API OpenRouter não está configurada no painel.';
        }

        $messages = array_merge(
            [['role' => 'system', 'content' => $this->systemPrompt]],
            $history
        );

        $reply = $this->callWithFallback($messages, 0);

        if ($reply === null) {
            return 'Estou com dificuldades técnicas agora. Por favor, nos chame no WhatsApp! 💙';
        }

        return $reply;
    }

    /**
     * Tentativa recursiva com fallback entre modelos.
     * Equivalente ao modelIndex recursivo do server.js.
     */
    private function callWithFallback(array $messages, int $index): ?string
    {
        if ($index >= count($this->models)) {
            appLog('error', 'Todos os modelos OpenRouter falharam.');
            return null;
        }

        $model = $this->models[$index];
        appLog('info', "Tentando modelo [{$index}]: {$model}");

        try {
            $payload = json_encode([
                'model'      => $model,
                'messages'   => $messages,
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
            ]);

            $context = stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'timeout' => $this->timeout,
                    'header'  => implode("\r\n", [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $this->apiKey,
                        'HTTP-Referer: ' . APP_URL,
                        'X-Title: CH Higienizacoes Bot',
                    ]),
                    'content' => $payload,
                    'ignore_errors' => true,
                ],
            ]);

            $raw = @file_get_contents($this->apiUrl, false, $context);

            if ($raw === false) {
                appLog('warning', "Modelo [{$model}] falhou (sem resposta). Próximo...");
                return $this->callWithFallback($messages, $index + 1);
            }

            $data = json_decode($raw, true);

            if (!empty($data['error'])) {
                appLog('warning', "Modelo [{$model}] erro: " . ($data['error']['message'] ?? 'desconhecido'));
                return $this->callWithFallback($messages, $index + 1);
            }

            $content = $data['choices'][0]['message']['content'] ?? null;
            if (!$content) {
                appLog('warning', "Modelo [{$model}] sem choices. Próximo...");
                return $this->callWithFallback($messages, $index + 1);
            }

            appLog('info', "Resposta obtida com: {$model}");
            return $content;

        } catch (\Throwable $e) {
            appLog('warning', "Modelo [{$model}] exceção: " . $e->getMessage());
            return $this->callWithFallback($messages, $index + 1);
        }
    }

    /**
     * Prompt padrão caso o arquivo não exista (cópia fiel do prompt.txt original).
     */
    private function defaultPrompt(): string
    {
        return <<<PROMPT
Você é a Ana, assistente virtual da CH Higienizações (Charles Higienizações).

Você trabalha dentro do site oficial da empresa e deve orientar os clientes com base nas funcionalidades disponíveis no site.

A CH Higienizações é uma empresa baiana fundada em 2025, especializada em higienização profissional de estofados, sofás, colchões, tapetes e interiores de veículos, atuando em Esplanada e região.

Seu papel é atender clientes de forma educada, clara, objetiva e natural, ajudando com informações sobre os serviços e auxiliando no processo de agendamento dentro do próprio site.

FORMA DE RESPOSTA:
- Sempre responda com mensagens curtas e objetivas.
- Fale como uma pessoa real, simpática e descontraída.
- Use linguagem natural e informal, com respeito.
- Evite textos longos e explicações desnecessárias.
- Use emoji apenas quando fizer sentido.
- Não use sempre as mesmas frases.

COMPORTAMENTO GERAL:
- Nunca responda perguntas fora do contexto da empresa.
- Se o cliente sair do assunto, diga de forma natural que só pode ajudar com serviços da CH Higienizações.
- Nunca invente informações.
- Não informe preços ou prazos se não forem fornecidos.
- Você faz parte do site e deve guiar o cliente dentro dele.
- Nunca repita saudações se a conversa já começou.

MENSAGEM INICIAL:
"Olá! 😊 Posso te ajudar com um serviço ou agendamento?"

CONTEXTO DA EMPRESA:
A CH Higienizações atende a domicílio em Esplanada e região, oferecendo higienização profissional com foco em qualidade, cuidado e satisfação do cliente.
PROMPT;
    }
}
