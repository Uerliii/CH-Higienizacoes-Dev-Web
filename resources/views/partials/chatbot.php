<!-- Chatbot CTA Fix -->
<aside class="chatbot-widget" aria-label="Assistente virtual">
    <button class="bot-btn pulse-glow" id="bot-toggle" aria-label="Abrir assistente virtual" aria-expanded="false"
        aria-controls="bot-panel">
        <i class="ph-fill ph-chat-circle-text" aria-hidden="true"></i>
    </button>
    <div class="bot-panel" id="bot-panel" role="dialog" aria-modal="true" aria-label="Chat com assistente CH">
        <header class="bot-header">
            <div class="bot-avatar" aria-hidden="true"><i class="ph-fill ph-robot"></i></div>
            <div>
                <strong>Assistente CH</strong>
                <span>Online agora</span>
            </div>
            <button class="close-bot" id="close-bot" aria-label="Fechar assistente">
                <i class="ph ph-x" aria-hidden="true"></i>
            </button>
        </header>
        <div class="bot-messages" id="bot-messages" role="log" aria-live="polite"
            aria-label="Mensagens do assistente">
            <div class="message bot-msg">
                Olá! Sou o assistente da CH Higienizações 👋
            </div>
            <div class="message bot-msg">
                Posso te ajudar a agendar ou tirar alguma dúvida?
            </div>
        </div>
        <div class="bot-input-area">
            <input type="text" id="bot-input" placeholder="Digite sua mensagem..."
                style="flex: 1; padding: 0.5rem; border: 1px solid var(--border); border-radius: 20px; outline: none; font-family: inherit;">
            <button id="bot-send" aria-label="Enviar mensagem"
                style="background: var(--primary); color: white; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                <i class="ph-fill ph-paper-plane-right" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</aside>
