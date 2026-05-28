<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
require_once dirname(__DIR__, 2) . '/backend/Models/Database.php';
$pdo = \Models\Database::connection();

$total = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn() ?: 0;
$pendentes = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pendente'")->fetchColumn() ?: 0;
$confirmados = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmado'")->fetchColumn() ?: 0;
$receita = $pdo->query("SELECT SUM(price) FROM bookings WHERE status = 'pago'")->fetchColumn() ?: 0;

$recentes = $pdo->query("SELECT * FROM bookings ORDER BY id DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin Premium - CH Higienizações</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'sans-serif'] },
                    colors: {
                        primary: { 50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 600: '#2563eb', 900: '#1e3a8a' },
                        dark: '#0f172a'
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #f8fafc; }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); }
        .sidebar-gradient { background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%); }
        .icon-gradient-blue { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
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
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-blue-600/10 text-blue-400 border border-blue-500/20 shadow-inner transition-all font-medium">
                    <i class="ph-fill ph-squares-four text-xl"></i> Visão Geral
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
                <a href="chatbot.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-white/5 text-slate-300 hover:text-white transition-all font-medium group">
                    <i class="ph ph-robot text-xl group-hover:text-purple-400 transition-colors"></i> Chatbot IA
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
        <!-- Decorator Blob -->
        <div class="absolute top-0 left-0 w-full h-64 bg-gradient-to-b from-blue-50 to-transparent -z-10"></div>

        <!-- Header -->
        <header class="h-20 flex items-center justify-between px-10 z-10">
            <div class="flex items-center gap-4">
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Bem-vindo de volta! 👋</h1>
            </div>
            <div class="flex items-center gap-5">
                <button onclick="window.simulateNewAppointment()" class="px-5 py-2.5 bg-white border border-blue-100 text-blue-600 rounded-full text-sm font-bold hover:shadow-lg hover:shadow-blue-500/20 transition-all flex items-center gap-2">
                    <i class="ph-fill ph-bell-ringing text-lg"></i> Simular Notificação
                </button>
            </div>
        </header>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto px-10 pb-10">
            
            <!-- Resumo Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-10 mt-4">
                <!-- Card 1 -->
                <div class="glass-card rounded-2xl p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-500 z-0"></div>
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-sm font-semibold text-slate-500 mb-1">Total de Agendamentos</p>
                            <h3 class="text-4xl font-bold text-slate-800"><?= $total ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                            <i class="ph-fill ph-files text-2xl"></i>
                        </div>
                    </div>
                    <div class="relative z-10 mt-4 text-sm font-medium text-green-500 flex items-center gap-1">
                        <i class="ph-bold ph-trend-up"></i> +12% este mês
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="glass-card rounded-2xl p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-orange-50 rounded-full group-hover:scale-150 transition-transform duration-500 z-0"></div>
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-sm font-semibold text-slate-500 mb-1">Aguardando Confirmação</p>
                            <h3 class="text-4xl font-bold text-slate-800"><?= $pendentes ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-400 to-red-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-orange-500/30">
                            <i class="ph-fill ph-clock text-2xl"></i>
                        </div>
                    </div>
                    <div class="relative z-10 mt-4 text-sm font-medium text-orange-500 flex items-center gap-1">
                        <i class="ph-bold ph-warning-circle"></i> Requerem atenção
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="glass-card rounded-2xl p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-green-50 rounded-full group-hover:scale-150 transition-transform duration-500 z-0"></div>
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-sm font-semibold text-slate-500 mb-1">Confirmados Hoje</p>
                            <h3 class="text-4xl font-bold text-slate-800"><?= $confirmados ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-emerald-500/30">
                            <i class="ph-fill ph-check-circle text-2xl"></i>
                        </div>
                    </div>
                    <div class="relative z-10 mt-4 text-sm font-medium text-slate-400 flex items-center gap-1">
                        Prontos para atendimento
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="glass-card rounded-2xl p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-purple-50 rounded-full group-hover:scale-150 transition-transform duration-500 z-0"></div>
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-sm font-semibold text-slate-500 mb-1">Receita (Serviços Pagos)</p>
                            <h3 class="text-4xl font-bold text-slate-800"><span class="text-2xl text-slate-400">R$</span> <?= number_format($receita, 2, ',', '.') ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-purple-500/30">
                            <i class="ph-fill ph-wallet text-2xl"></i>
                        </div>
                    </div>
                    <div class="relative z-10 mt-4 text-sm font-medium text-purple-500 flex items-center gap-1">
                        Total de negócios concluídos
                    </div>
                </div>
            </div>

            <!-- Recentes Tabela -->
            <div class="glass-card rounded-2xl overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-white/50">
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Chegaram Recentemente</h2>
                        <p class="text-sm text-slate-500">Últimos clientes que preencheram o formulário.</p>
                    </div>
                    <a href="agendamentos.php" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-bold hover:bg-slate-200 transition-colors">Ver Todos</a>
                </div>
                
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-xs font-bold uppercase tracking-wider">
                            <th class="px-8 py-4">Cliente</th>
                            <th class="px-8 py-4">Serviço Solicitado</th>
                            <th class="px-8 py-4">Data Preferencial</th>
                            <th class="px-8 py-4">Status</th>
                            <th class="px-8 py-4 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php foreach($recentes as $app): ?>
                        <tr class="hover:bg-slate-50/80 transition group">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center font-bold text-blue-600 border border-blue-200">
                                        <?= substr($app['nome'], 0, 1) ?>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 text-base"><?= htmlspecialchars($app['nome']) ?></p>
                                        <p class="text-xs font-medium text-slate-500 flex items-center gap-1 mt-0.5">
                                            <i class="ph-fill ph-whatsapp-logo text-green-500"></i> <?= htmlspecialchars($app['whatsapp']) ?>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="font-semibold text-slate-700"><?= ucfirst($app['service']) ?></span>
                            </td>
                            <td class="px-8 py-5">
                                <p class="font-bold text-slate-800"><?= date('d/m/Y', strtotime($app['data_agendamento'])) ?></p>
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide"><?= htmlspecialchars($app['horario']) ?></p>
                            </td>
                            <td class="px-8 py-5">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-bold border border-gray-200">
                                    <?= ucfirst($app['status']) ?>
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <a href="ver_cliente.php?id=<?= $app['id'] ?>" class="inline-flex items-center justify-center w-10 h-10 bg-white border border-slate-200 text-slate-600 rounded-xl hover:border-blue-500 hover:text-blue-600 hover:shadow-md transition-all">
                                    <i class="ph-bold ph-arrow-right text-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($recentes)): ?>
                        <tr>
                            <td colspan="5" class="px-8 py-8 text-center text-slate-400">Nenhum agendamento ainda.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Notificações Toast -->
    <div id="toast-container" class="fixed bottom-8 right-8 z-50 flex flex-col gap-4"></div>

    <script>
        function showRealTimeToast(data) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            toast.className = 'glass-card p-5 w-96 rounded-2xl border-l-4 border-l-blue-500 transform transition-all translate-x-[120%] duration-500 flex items-start gap-4 relative overflow-hidden';
            
            toast.innerHTML = `
                <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-blue-500/10 to-purple-500/10 rounded-full -mr-10 -mt-10 blur-xl"></div>
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 shrink-0">
                    <i class="ph-fill ph-bell-ringing text-2xl"></i>
                </div>
                <div class="flex-1 relative z-10">
                    <h4 class="font-bold text-slate-800 text-base mb-1">${data.message}</h4>
                    <p class="text-sm font-semibold text-slate-600">${data.name}</p>
                    <p class="text-xs font-medium text-slate-500 flex items-center gap-1 mt-1">
                        <i class="ph-bold ph-sparkle text-blue-500"></i> ${data.service}
                    </p>
                </div>
                <button class="text-slate-400 hover:text-slate-600 transition" onclick="this.parentElement.remove()">
                    <i class="ph-bold ph-x"></i>
                </button>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => toast.classList.remove('translate-x-[120%]'), 10);
            
            setTimeout(() => {
                toast.classList.add('translate-x-[120%]');
                setTimeout(() => toast.remove(), 500);
            }, 6000);
        }

        window.simulateNewAppointment = function() {
            showRealTimeToast({
                message: 'Novo Agendamento!',
                name: 'Carlos Alberto (Site)',
                service: 'Limpeza de Colchão King',
            });
        }
    </script>
</body>
</html>
