<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
require_once dirname(__DIR__, 2) . '/backend/Models/Database.php';
$pdo = \Models\Database::connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['prompt'])) {
        $stmt = $pdo->prepare("INSERT INTO settings (`key_name`, `value`) VALUES ('chatbot_prompt', ?) ON DUPLICATE KEY UPDATE `value` = ?");
        $stmt->execute([$_POST['prompt'], $_POST['prompt']]);
        $success = "Configurações atualizadas com sucesso no Banco de Dados!";
    }
    if (isset($_POST['api_key'])) {
        $stmt = $pdo->prepare("INSERT INTO settings (`key_name`, `value`) VALUES ('openrouter_api_key', ?) ON DUPLICATE KEY UPDATE `value` = ?");
        $stmt->execute([$_POST['api_key'], $_POST['api_key']]);
    }
    if (isset($_POST['gemini_key'])) {
        $stmt = $pdo->prepare("INSERT INTO settings (`key_name`, `value`) VALUES ('gemini_api_key', ?) ON DUPLICATE KEY UPDATE `value` = ?");
        $stmt->execute([$_POST['gemini_key'], $_POST['gemini_key']]);
    }
    if (isset($_POST['ai_provider'])) {
        $stmt = $pdo->prepare("INSERT INTO settings (`key_name`, `value`) VALUES ('ai_provider', ?) ON DUPLICATE KEY UPDATE `value` = ?");
        $stmt->execute([$_POST['ai_provider'], $_POST['ai_provider']]);
    }
}

// Carregar configurações atuais do banco
$stmt = $pdo->query("SELECT `key_name`, `value` FROM settings WHERE `key_name` IN ('chatbot_prompt', 'openrouter_api_key', 'gemini_api_key', 'ai_provider')");
$settings = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

$currentPrompt = $settings['chatbot_prompt'] ?? "Você é a Ana, assistente virtual da CH Higienizações...";
$currentApiKey = $settings['openrouter_api_key'] ?? '';
$currentGeminiKey = $settings['gemini_api_key'] ?? '';
$currentProvider = $settings['ai_provider'] ?? 'openrouter';

// Fetch chat logs grouped by session
$stmt = $pdo->query("SELECT session_id, COUNT(*) as messages_count, MAX(created_at) as last_activity FROM chat_logs GROUP BY session_id ORDER BY last_activity DESC");
$sessions = $stmt->fetchAll() ?: [];

// Fetch specific session
$viewSession = $_GET['session'] ?? null;
$messages = [];
if ($viewSession) {
    $stmt = $pdo->prepare("SELECT * FROM chat_logs WHERE session_id = ? ORDER BY id ASC");
    $stmt->execute([$viewSession]);
    $messages = $stmt->fetchAll();
}

$pendentes = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pendente'")->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle do Chatbot - CH Higienizações</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Outfit', 'sans-serif'] } } } }
    </script>
    <style>
        body { background-color: #f8fafc; }
        .glass-card { background: rgba(255, 255, 255, 0.95); border: 1px solid rgba(255,255,255,0.2); box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); }
        .sidebar-gradient { background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%); }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-slate-800">

    <!-- Sidebar Premium -->
    <aside class="w-72 sidebar-gradient text-white flex flex-col shadow-2xl relative z-20">
        <div class="p-8 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                <i class="ph-bold ph-sparkle text-white text-xl"></i>
            </div>
            <h2 class="text-2xl font-bold tracking-tight">CH<span class="text-blue-400">Admin</span></h2>
        </div>
        <div class="px-6 pb-4">
            <nav class="space-y-2">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-white/5 text-slate-300 transition-all font-medium">
                    <i class="ph ph-squares-four text-xl"></i> Visão Geral
                </a>
                <a href="agendamentos.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-white/5 text-slate-300 hover:text-white transition-all font-medium group">
                    <i class="ph ph-calendar-check text-xl group-hover:text-blue-400 transition-colors"></i> Agendamentos
                    <?php if($pendentes > 0): ?>
                    <span class="ml-auto bg-blue-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-lg shadow-blue-500/30"><?= $pendentes ?></span>
                    <?php endif; ?>
                </a>
                <a href="agendamentos.php?view=clientes" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-white/5 text-slate-300 hover:text-white transition-all font-medium group">
                    <i class="ph ph-users text-xl group-hover:text-blue-400 transition-colors"></i> Clientes
                </a>
                <a href="chatbot.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-purple-600/10 text-purple-400 border border-purple-500/20 font-medium">
                    <i class="ph-fill ph-robot text-xl"></i> Controle do Chatbot
                </a>
            </nav>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col overflow-hidden relative">
        <header class="h-20 flex items-center justify-between px-10 z-10 border-b border-slate-200 bg-white/50 backdrop-blur-md">
            <div>
                <h1 class="text-xl font-bold text-slate-800 tracking-tight">Centro de Comando IA</h1>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Edição de Prompt & Auditoria</p>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto px-10 py-8">
            <?php if(!empty($success)): ?>
            <div class="mb-6 p-4 bg-green-50 text-green-700 border border-green-200 rounded-xl font-bold">
                <?= $success ?>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                
                <!-- Coluna Esquerda: Edição de Prompt -->
                <div class="space-y-6">
                    <div class="glass-card rounded-2xl overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3 bg-white">
                            <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center"><i class="ph-bold ph-terminal-window text-lg"></i></div>
                            <h2 class="font-bold text-slate-800">Prompt do Sistema (Comportamento)</h2>
                        </div>
                        <form method="POST" class="p-6 bg-white/50 space-y-4">
                            <p class="text-sm text-slate-500 mb-2">Este texto define a personalidade, regras e conhecimentos da assistente Ana.</p>
                            <textarea name="prompt" rows="12" class="w-full px-4 py-3 bg-slate-900 text-green-400 border border-slate-800 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none text-sm font-mono shadow-inner transition-all resize-none leading-relaxed"><?= htmlspecialchars($currentPrompt) ?></textarea>
                            
                            <hr class="my-4 border-slate-200/50">
                            
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1">Chave API OpenRouter</label>
                                    <input type="text" name="api_key" value="<?= htmlspecialchars($currentApiKey) ?>" placeholder="sk-or-v1-..." class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none text-sm font-mono shadow-sm transition-all">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1">Chave API Gemini</label>
                                    <input type="text" name="gemini_key" value="<?= htmlspecialchars($currentGeminiKey) ?>" placeholder="AIzaSy..." class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none text-sm font-mono shadow-sm transition-all">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Qual provedor de IA utilizar?</label>
                                <div class="flex items-center gap-6">
                                    <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-slate-700">
                                        <input type="radio" name="ai_provider" value="openrouter" class="w-4 h-4 text-purple-600 border-gray-300 focus:ring-purple-500" <?= $currentProvider === 'openrouter' ? 'checked' : '' ?>>
                                        OpenRouter
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-slate-700">
                                        <input type="radio" name="ai_provider" value="gemini" class="w-4 h-4 text-purple-600 border-gray-300 focus:ring-purple-500" <?= $currentProvider === 'gemini' ? 'checked' : '' ?>>
                                        Google Gemini Direto
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="w-full py-3.5 bg-purple-600 text-white font-bold rounded-xl hover:bg-purple-700 transition shadow-lg shadow-purple-500/30 flex items-center justify-center gap-2">
                                <i class="ph-bold ph-floppy-disk text-lg"></i> Salvar Configurações
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Coluna Direita: Logs de Conversa -->
                <div class="space-y-6 flex flex-col h-[calc(100vh-140px)]">
                    <?php if ($viewSession): ?>
                        <div class="glass-card rounded-2xl flex flex-col flex-1 overflow-hidden">
                            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-white">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center"><i class="ph-bold ph-chats text-lg"></i></div>
                                    <h2 class="font-bold text-slate-800">Sessão: <?= substr($viewSession, 0, 8) ?>...</h2>
                                </div>
                                <a href="chatbot.php" class="text-sm text-blue-600 hover:underline font-bold">Voltar</a>
                            </div>
                            <div class="p-6 bg-slate-50 flex-1 overflow-y-auto space-y-4">
                                <?php foreach($messages as $msg): ?>
                                    <?php if($msg['role'] == 'user'): ?>
                                        <div class="flex justify-end">
                                            <div class="max-w-[80%] bg-blue-600 text-white rounded-2xl rounded-tr-sm px-4 py-2.5 text-sm shadow-sm">
                                                <?= nl2br(htmlspecialchars($msg['content'])) ?>
                                            </div>
                                        </div>
                                    <?php elseif($msg['role'] == 'assistant'): ?>
                                        <div class="flex justify-start">
                                            <div class="max-w-[80%] bg-white border border-slate-200 text-slate-700 rounded-2xl rounded-tl-sm px-4 py-2.5 text-sm shadow-sm">
                                                <?= nl2br(htmlspecialchars($msg['content'])) ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="glass-card rounded-2xl overflow-hidden">
                            <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3 bg-white">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center"><i class="ph-bold ph-users text-lg"></i></div>
                                <h2 class="font-bold text-slate-800">Auditoria de Conversas</h2>
                            </div>
                            <div class="overflow-y-auto max-h-[600px]">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-gray-100">
                                            <th class="px-6 py-4 font-bold">ID Sessão</th>
                                            <th class="px-6 py-4 font-bold">Mensagens</th>
                                            <th class="px-6 py-4 font-bold">Última Ativ.</th>
                                            <th class="px-6 py-4 font-bold text-right">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 text-sm">
                                        <?php foreach($sessions as $sess): ?>
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="px-6 py-4 font-mono text-slate-500"><?= substr($sess['session_id'], 0, 10) ?>...</td>
                                            <td class="px-6 py-4"><span class="px-2.5 py-1 bg-slate-100 rounded-lg font-bold text-slate-600"><?= $sess['messages_count'] ?></span></td>
                                            <td class="px-6 py-4 text-slate-500 font-medium"><?= date('d/m H:i', strtotime($sess['last_activity'])) ?></td>
                                            <td class="px-6 py-4 text-right">
                                                <a href="chatbot.php?session=<?= $sess['session_id'] ?>" class="text-blue-600 hover:underline font-bold">Ler</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if(empty($sessions)): ?>
                                        <tr><td colspan="4" class="p-8 text-center text-slate-400">Nenhuma conversa registrada ainda.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>
</body>
</html>
