# CH Higienizações 🫧

Um sistema web completo desenvolvido em **PHP com arquitetura MVC**, projetado para uma empresa de higienização de estofados. Este projeto inclui uma Landing Page focada em conversão com agendamento online (Wizard) e um **Chatbot com Inteligência Artificial** integrado, além de um Painel Administrativo seguro para a gestão do negócio.

---

## 🚀 Funcionalidades Principais

### Visão do Cliente (Frontend)
- **Design Moderno e Responsivo:** Otimizado para conversões em dispositivos móveis e desktops.
- **Agendamento Inteligente (Wizard):** Formulário em etapas que reduz o abandono e coleta o serviço desejado (Sofá, Colchão, Carro, etc).
- **Chatbot Integrado com IA:** Um assistente virtual inteligente ("Ana") que tira dúvidas e ajuda o cliente 24/7, conectado às APIs do Google Gemini ou OpenRouter.
- **Redirecionamento Rápido:** Fallback amigável para o WhatsApp caso a IA passe por instabilidades.

### Visão do Proprietário (Painel Admin)
- **Gestão de Agendamentos (Leads):** Controle em tempo real das solicitações de serviço feitas pelo site.
- **Controle da Inteligência Artificial:** 
  - Troca dinâmica de chaves de API sem precisar mexer no código.
  - Alternância entre provedores de IA (`Gemini` e `OpenRouter`) com apenas um clique.
  - Edição do *Prompt* (comportamento/personalidade do chatbot).
- **Auditoria de Conversas:** Histórico completo de tudo que os clientes conversam com a Inteligência Artificial no site.

---

## 📂 Arquitetura do Projeto (MVC Padrão)

O projeto foi construído seguindo rigorosos padrões de mercado, separando a lógica de negócio da interface gráfica. **Nenhum arquivo sensível fica exposto na internet.**

```text
CH_Higienizacoes/
│
├── public/                 # Document Root (Única pasta exposta publicamente)
│   ├── index.php           # Front Controller: Recebe e despacha todas as requisições
│   ├── admin/              # Telas do painel administrativo
│   └── assets/             # CSS, Javascript (incluindo a lógica do chat) e Imagens
│
├── backend/                # "O Cérebro" - Regras de Negócio e Lógica
│   ├── Controllers/        # Recebem a requisição, processam e retornam a View (Ex: ChatController)
│   ├── Models/             # Conexões seguras (PDO Singleton) com o Banco de Dados MySQL
│   └── Services/           # Integrações externas (APIs do Gemini e OpenRouter)
│
├── resources/views/        # "O Rosto" - Templates HTML/PHP
│   ├── layouts/app.php     # Molde base estrutural das páginas
│   └── partials/           # Pedaços modulares (Hero, Form, Footer, Chatbot)
│
├── routes/                 # "O Mapa"
│   └── web.php             # Roteador central. Define as URLs da aplicação.
│
├── config/                 # Configurações globais e textos padrão do sistema
├── database/               # Scripts SQL para criação do banco e Seeders de testes
└── .env.example            # Modelo de configuração das variáveis de ambiente
```

---

## 🛠️ Como instalar e rodar o projeto

### 1. Requisitos
- Servidor Web (XAMPP, Laragon, Apache, ou Hospedagem cPanel/Hostinger)
- PHP 8.0 ou superior
- MySQL / MariaDB

### 2. Configuração do Banco de Dados
1. Crie um banco de dados vazio no seu MySQL.
2. Importe o arquivo `database/migrations/001_create_bookings_table.sql` (ou o dump completo se fornecido) para criar a estrutura das tabelas.
3. (Opcional) Rode o arquivo `database/seeders/BookingSeeder.php` via terminal para popular o sistema com dados falsos para teste.

### 3. Configuração do Ambiente
1. Duplique o arquivo `.env.example` e renomeie a cópia para `.env`.
2. Abra o arquivo `.env` e preencha com as credenciais do seu banco de dados:
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_seu_banco
DB_USERNAME=root
DB_PASSWORD=sua_senha
```
*(Nota: O arquivo `.env` verdadeiro está protegido no `.gitignore` para nunca vazar suas chaves na internet).*

### 4. Rodando o sistema
Configure o diretório raiz do seu servidor Apache (Document Root) para apontar para a pasta `/public`.
Acesse `http://localhost/ch-higienizacoes/public/` (ou a URL do seu servidor) e pronto!

---

## 🤖 Como configurar a IA (Chatbot)

Após entrar no Painel Administrativo, navegue até a seção do **Chatbot**:
1. Cadastre-se gratuitamente no [Google AI Studio](https://aistudio.google.com/) ou no [OpenRouter](https://openrouter.ai/).
2. Gere sua Chave de API.
3. Cole a chave na configuração do painel e salve.
4. Ajuste o comportamento da "Ana" no campo "Prompt".

Em caso de sobrecarga dos servidores do Google (Erro 503 Overloaded) ou chaves inválidas (Erro 403), o sistema entra automaticamente no "Plano B", exibindo uma mensagem educada de sobrecarga e indicando o WhatsApp para não perder o cliente.

---
*Projeto desenvolvido para a disciplina de Dev Web/Mobile.*
