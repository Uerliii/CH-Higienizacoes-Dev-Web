<?php

namespace Services;

class GeminiService
{
    private string $apiKey;
    private string $systemPrompt;
    private string $model;
    private float $temperature;
    private int $maxOutputTokens;

    public function __construct()
    {
        // Carrega configurações do banco de dados
        require_once dirname(__DIR__, 2) . '/backend/Models/Database.php';
        try {
            $pdo = \Models\Database::connection();
            $stmt = $pdo->query("SELECT `key_name`, `value` FROM settings WHERE `key_name` IN ('gemini_api_key', 'chatbot_prompt', 'gemini_model', 'gemini_temperature', 'gemini_max_tokens')");
            $settings = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
        } catch (\Exception $e) {
            $settings = [];
        }

        $this->apiKey = $settings['gemini_api_key'] ?? '';
        $this->model = $settings['gemini_model'] ?? 'gemini-3-flash-preview';
        $this->temperature = isset($settings['gemini_temperature']) ? (float)$settings['gemini_temperature'] : 0.4;
        $this->maxOutputTokens = isset($settings['gemini_max_tokens']) ? (int)$settings['gemini_max_tokens'] : 250;
        
        if (!empty($settings['chatbot_prompt'])) {
            $this->systemPrompt = $settings['chatbot_prompt'];
        } else {
            $promptFile = BASE_PATH . '/config/prompt.txt';
            $this->systemPrompt = file_exists($promptFile) ? file_get_contents($promptFile) : $this->defaultPrompt();
        }
    }

    public function chat(array $history): string
    {
        if (empty($this->apiKey)) {
            appLog('error', 'Chave da API Gemini não configurada.');
            return 'A chave da API Gemini não está configurada no painel.';
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        // Converter o formato OpenRouter/OpenAI para o formato do Gemini
        $contents = [];
        foreach ($history as $msg) {
            // Ignora o system no history (se houver), pois o Gemini usa systemInstruction
            if ($msg['role'] === 'system') continue;
            
            // Gemini usa 'user' e 'model' (em vez de 'assistant')
            $role = ($msg['role'] === 'assistant') ? 'model' : 'user';
            
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => (string) $msg['content']]]
            ];
        }

        $payload = [
            'systemInstruction' => [
                'parts' => [
                    ['text' => $this->systemPrompt]
                ]
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $this->temperature,
                'maxOutputTokens' => $this->maxOutputTokens
            ]
        ];

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => json_encode($payload),
                'timeout' => 15,
                'ignore_errors' => true
            ]
        ]);

        $rawResponse = @file_get_contents($url, false, $context);
        
        if ($rawResponse === false) {
            appLog('error', 'Falha ao conectar com a API Gemini.');
            return 'Estou com dificuldades técnicas agora. Por favor, nos chame no WhatsApp! 💙';
        }

        $data = json_decode($rawResponse, true);

        if (isset($data['error'])) {
            appLog('error', 'Erro da API Gemini: ' . ($data['error']['message'] ?? 'Desconhecido'));
            return 'Estou com muita demanda no momento e não consegui processar sua mensagem. Por favor, tente novamente em instantes ou nos chame no WhatsApp! 💙';
        }

        $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$reply) {
            appLog('error', 'Resposta vazia da API Gemini: ' . $rawResponse);
            return 'Desculpe, não consegui formular uma resposta.';
        }

        appLog('info', "Resposta obtida com sucesso via Gemini ({$this->model})");
        return $reply;
    }

    private function defaultPrompt(): string
    {
        return "Você é a Ana, assistente virtual da CH Higienizações...";
    }
}
