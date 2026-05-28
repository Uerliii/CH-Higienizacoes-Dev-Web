<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
require_once dirname(__DIR__, 2) . '/backend/Models/Database.php';
$pdo = \Models\Database::connection();

$appointments = $pdo->query("SELECT * FROM bookings ORDER BY data_agendamento ASC")->fetchAll();

// Clients logic (grouping by phone/whatsapp)
$clientes = $pdo->query("SELECT nome, whatsapp, COUNT(*) as qtd_servicos FROM bookings GROUP BY whatsapp ORDER BY id DESC")->fetchAll();
$pendentes = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pendente'")->fetchColumn() ?: 0;

$viewMode = $_GET['view'] ?? 'agendamentos';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamentos - CH Higienizações</title>
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
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Menu Principal</p>
            <nav class="space-y-2">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-white/5 text-slate-300 hover:text-white transition-all font-medium">
                    <i class="ph-fill ph-squares-four text-xl"></i> Visão Geral
                </a>
                
                <a href="agendamentos.php?view=agendamentos" class="flex items-center gap-3 px-4 py-3.5 rounded-xl <?= $viewMode == 'agendamentos' ? 'bg-blue-600/10 text-blue-400 border border-blue-500/20 shadow-inner' : 'hover:bg-white/5 text-slate-300 hover:text-white' ?> transition-all font-medium">
                    <i class="ph ph-calendar-check text-xl"></i> Agendamentos
                    <?php if($pendentes > 0): ?>
                    <span class="ml-auto bg-blue-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-lg shadow-blue-500/30"><?= $pendentes ?></span>
                    <?php endif; ?>
                </a>
                
                <a href="agendamentos.php?view=clientes" class="flex items-center gap-3 px-4 py-3.5 rounded-xl <?= $viewMode == 'clientes' ? 'bg-blue-600/10 text-blue-400 border border-blue-500/20 shadow-inner' : 'hover:bg-white/5 text-slate-300 hover:text-white' ?> transition-all font-medium">
                    <i class="ph ph-users text-xl"></i> Clientes
                </a>

                <a href="chatbot.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-white/5 text-slate-300 hover:text-white transition-all font-medium">
                    <i class="ph ph-robot text-xl"></i> Chatbot IA
                </a>
            </nav>
        </div>

        <div class="mt-auto p-6 border-t border-white/10">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                    <?= substr($_SESSION['admin_name'] ?? 'A', 0, 1) ?>
                </div>
                <div>
                    <p class="text-sm font-bold text-white"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Administrador') ?></p>
                    <p class="text-xs text-slate-400"><?= htmlspecialchars($_SESSION['admin_email'] ?? 'admin@chhigienizacoes') ?></p>
                </div>
            </div>
            <a href="logout.php" class="flex items-center justify-center gap-2 w-full py-2.5 rounded-lg bg-white/5 hover:bg-red-500/10 text-slate-300 hover:text-red-400 transition-all text-sm font-medium">
                <i class="ph ph-sign-out text-lg"></i> Encerrar Sessão
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col overflow-hidden relative">
        <div class="absolute top-0 left-0 w-full h-64 bg-gradient-to-b from-slate-100 to-transparent -z-10"></div>

        <header class="h-20 flex items-center justify-between px-10 z-10 border-b border-slate-200 bg-white/50 backdrop-blur-md">
            <div>
                <h1 class="text-xl font-bold text-slate-800 tracking-tight"><?= $viewMode == 'clientes' ? 'Meus Clientes' : 'Todos os Agendamentos' ?></h1>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Listagem dinâmica puxada do banco</p>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto px-10 py-8">
            <div class="glass-card rounded-2xl overflow-hidden">
                <div class="px-8 py-5 border-b border-slate-100 bg-white flex justify-between items-center">
                    <h2 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="ph-fill <?= $viewMode == 'clientes' ? 'ph-users' : 'ph-calendar-check' ?> text-blue-500 text-xl"></i>
                        <?= $viewMode == 'clientes' ? 'Lista de Clientes Únicos' : 'Agenda Completa' ?>
                    </h2>
                </div>
                
                <table class="w-full text-left">
                    <thead>
                        <?php if($viewMode == 'clientes'): ?>
                        <tr class="bg-slate-50/50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                            <th class="px-8 py-4">Cliente</th>
                            <th class="px-8 py-4">WhatsApp</th>
                            <th class="px-8 py-4">Serviços Solicitados</th>
                        </tr>
                        <?php else: ?>
                        <tr class="bg-slate-50/50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                            <th class="px-8 py-4">Protocolo</th>
                            <th class="px-8 py-4">Cliente</th>
                            <th class="px-8 py-4">Serviço</th>
                            <th class="px-8 py-4">Data/Hora</th>
                            <th class="px-8 py-4">Status</th>
                            <th class="px-8 py-4 text-right">Ação</th>
                        </tr>
                        <?php endif; ?>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm bg-white/50">
                        <?php if($viewMode == 'clientes'): ?>
                            <?php foreach($clientes as $cli): ?>
                            <tr class="hover:bg-slate-50/80 transition group">
                                <td class="px-8 py-5 font-bold text-slate-800 text-base"><?= htmlspecialchars($cli['nome']) ?></td>
                                <td class="px-8 py-5">
                                    <span class="inline-flex items-center gap-1.5 text-green-600 font-bold bg-green-50 px-3 py-1 rounded-full text-sm border border-green-100">
                                        <i class="ph-fill ph-whatsapp-logo text-lg"></i> <?= htmlspecialchars($cli['whatsapp']) ?>
                                    </span>
                                </td>
                                <td class="px-8 py-5 font-semibold text-slate-600"><?= $cli['qtd_servicos'] ?> serviço(s)</td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($clientes)): ?>
                            <tr><td colspan="3" class="px-8 py-10 text-center text-slate-400 font-medium">Nenhum cliente registrado.</td></tr>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php foreach($appointments as $app): ?>
                            <tr class="hover:bg-slate-50/80 transition group">
                                <td class="px-8 py-5 text-slate-400 font-mono text-xs font-bold">#<?= str_pad($app['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td class="px-8 py-5">
                                    <p class="font-bold text-slate-800 text-base"><?= htmlspecialchars($app['nome']) ?></p>
                                    <p class="text-xs font-medium text-slate-500 flex items-center gap-1 mt-0.5">
                                        <i class="ph-fill ph-whatsapp-logo text-green-500"></i> <?= htmlspecialchars($app['whatsapp']) ?>
                                    </p>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="font-semibold text-slate-700 flex items-center gap-1.5">
                                        <div class="w-6 h-6 rounded bg-indigo-50 flex items-center justify-center text-indigo-500"><i class="ph-fill ph-sparkle text-xs"></i></div>
                                        <?= ucfirst($app['service']) ?>
                                    </span>
                                </td>
                                <td class="px-8 py-5">
                                    <p class="font-bold text-slate-800"><?= date('d/m/Y', strtotime($app['data_agendamento'])) ?></p>
                                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mt-0.5"><?= htmlspecialchars($app['horario']) ?></p>
                                </td>
                                <td class="px-8 py-5">
                                    <?php 
                                        $colors = [
                                            'pendente' => 'bg-orange-50 text-orange-600 border-orange-100',
                                            'confirmado' => 'bg-green-50 text-green-600 border-green-100',
                                            'atendimento' => 'bg-blue-50 text-blue-600 border-blue-100',
                                            'pago' => 'bg-purple-50 text-purple-600 border-purple-100',
                                            'cancelado' => 'bg-red-50 text-red-600 border-red-100'
                                        ];
                                        $color = $colors[$app['status']] ?? 'bg-slate-50 text-slate-600 border-slate-200';
                                        $statusLabel = $app['status'] == 'pago' ? 'Pago' : ucfirst($app['status']);
                                    ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border <?= $color ?>">
                                        <?= $statusLabel ?>
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <a href="ver_cliente.php?id=<?= $app['id'] ?>" class="inline-flex items-center justify-center w-9 h-9 bg-white border border-slate-200 text-slate-600 rounded-xl hover:border-blue-500 hover:text-blue-600 hover:shadow-md transition-all">
                                        <i class="ph-bold ph-arrow-right text-base"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($appointments)): ?>
                            <tr><td colspan="6" class="px-8 py-10 text-center text-slate-400 font-medium">Nenhum agendamento ainda.</td></tr>
                            <?php endif; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
