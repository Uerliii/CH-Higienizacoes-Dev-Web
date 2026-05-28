<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
require_once dirname(__DIR__, 2) . '/backend/Models/Database.php';
$pdo = \Models\Database::connection();

$id = $_GET['id'] ?? null;
$pendentes = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pendente'")->fetchColumn() ?: 0;

if (!$id) {
    header("Location: agendamentos.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? 'pendente';
    $notes = $_POST['notes'] ?? '';
    $price = str_replace(['R$', '.', ' '], '', $_POST['price'] ?? '0');
    $price = str_replace(',', '.', $price);
    $price = (float) $price;

    $stmt = $pdo->prepare("UPDATE bookings SET status = ?, notes = ?, price = ? WHERE id = ?");
    $stmt->execute([$status, $notes, $price, $id]);
    
    $success = "Atualizado com sucesso!";
}

$app = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
$app->execute([$id]);
$app = $app->fetch();

if (!$app) {
    echo "Agendamento não encontrado.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Cliente - CH Higienizações</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Outfit', 'sans-serif'] } } }
        }
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
                <a href="agendamentos.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-blue-600/10 text-blue-400 border border-blue-500/20 font-medium">
                    <i class="ph-fill ph-calendar-check text-xl"></i> Agendamentos
                    <?php if($pendentes > 0): ?>
                    <span class="ml-auto bg-blue-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-lg shadow-blue-500/30"><?= $pendentes ?></span>
                    <?php endif; ?>
                </a>
                <a href="agendamentos.php?view=clientes" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-white/5 text-slate-300 hover:text-white transition-all font-medium group">
                    <i class="ph ph-users text-xl group-hover:text-blue-400 transition-colors"></i> Clientes
                </a>
                <a href="chatbot.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-white/5 text-slate-300 hover:text-white transition-all font-medium">
                    <i class="ph ph-robot text-xl"></i> Chatbot IA
                </a>
            </nav>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col overflow-hidden relative">
        <div class="absolute top-0 left-0 w-full h-64 bg-gradient-to-b from-slate-100 to-transparent -z-10"></div>

        <header class="h-20 flex items-center justify-between px-10 z-10 border-b border-slate-200 bg-white/50 backdrop-blur-md">
            <div class="flex items-center gap-4">
                <a href="agendamentos.php" class="w-10 h-10 bg-white border border-slate-200 rounded-full flex items-center justify-center text-slate-600 hover:bg-slate-100 hover:text-blue-600 transition-all shadow-sm">
                    <i class="ph-bold ph-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-800 tracking-tight">Protocolo #<?= str_pad($app['id'], 5, '0', STR_PAD_LEFT) ?></h1>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Criado em <?= date('d/m/Y H:i', strtotime($app['created_at'] ?? 'now')) ?></p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-orange-50 text-orange-600 rounded-full text-sm font-bold border border-orange-200">
                <?= ucfirst($app['status']) ?>
            </span>
        </header>

        <div class="flex-1 overflow-y-auto px-10 py-8">
            <?php if(!empty($success)): ?>
            <div class="mb-6 p-4 bg-green-50 text-green-700 border border-green-200 rounded-xl font-bold">
                <?= $success ?>
            </div>
            <?php endif; ?>
            <div class="max-w-5xl mx-auto grid grid-cols-1 xl:grid-cols-3 gap-8">
                
                <!-- Coluna Esquerda: Informações do Cliente -->
                <div class="xl:col-span-2 space-y-8">
                    
                    <!-- Card Cliente -->
                    <div class="glass-card rounded-2xl overflow-hidden">
                        <div class="px-8 py-5 border-b border-slate-100 flex items-center gap-3 bg-white">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center"><i class="ph-fill ph-user text-xl"></i></div>
                            <h2 class="text-lg font-bold text-slate-800">Ficha do Cliente</h2>
                        </div>
                        <div class="p-8 bg-white/50 grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Nome Completo</label>
                                <p class="text-xl font-bold text-slate-800"><?= htmlspecialchars($app['nome']) ?></p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Contato WhatsApp</label>
                                <div class="flex items-center gap-3">
                                    <p class="text-xl font-bold text-slate-800"><?= htmlspecialchars($app['whatsapp']) ?></p>
                                    <a href="https://wa.me/<?= preg_replace('/\D/', '', $app['whatsapp']) ?>" target="_blank" class="px-3 py-1.5 bg-green-50 text-green-600 hover:bg-green-500 hover:text-white border border-green-200 hover:border-green-500 rounded-lg text-xs font-bold transition-all flex items-center gap-1 shadow-sm">
                                        <i class="ph-fill ph-whatsapp-logo text-base"></i> Chamar
                                    </a>
                                </div>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Endereço / Localidade</label>
                                <p class="text-lg font-medium text-slate-700 flex items-center gap-2">
                                    <i class="ph-fill ph-map-pin text-red-400"></i> <?= htmlspecialchars($app['cidade']) ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Card Serviço -->
                    <div class="glass-card rounded-2xl overflow-hidden">
                        <div class="px-8 py-5 border-b border-slate-100 flex items-center gap-3 bg-white">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="ph-fill ph-sparkle text-xl"></i></div>
                            <h2 class="text-lg font-bold text-slate-800">Detalhes do Serviço</h2>
                        </div>
                        <div class="p-8 bg-white/50 grid grid-cols-1 sm:grid-cols-3 gap-8">
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Serviço Solicitado</label>
                                <div class="inline-flex items-center gap-3 px-5 py-3 rounded-xl bg-slate-800 text-white shadow-lg shadow-slate-800/20">
                                    <i class="ph-fill ph-armchair text-2xl text-blue-400"></i>
                                    <span class="text-lg font-bold"><?= ucfirst($app['service']) ?></span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Data Desejada</label>
                                <p class="text-xl font-bold text-slate-800"><?= date('d/m/Y', strtotime($app['data_agendamento'])) ?></p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Turno</label>
                                <p class="text-xl font-bold text-slate-800 uppercase"><?= htmlspecialchars($app['horario']) ?></p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Coluna Direita: Ações Administrativas -->
                <div class="space-y-6">
                    <div class="glass-card rounded-2xl overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3 bg-white">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center"><i class="ph-bold ph-sliders text-lg"></i></div>
                            <h2 class="font-bold text-slate-800">Gerenciar Pedido</h2>
                        </div>
                        <form method="POST" class="p-6 bg-white/50 space-y-6">
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Alterar Status</label>
                                    <select name="status" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-bold text-slate-700 shadow-sm transition-all cursor-pointer">
                                        <option value="pendente" <?= $app['status'] == 'pendente' ? 'selected' : '' ?>>⏳ Pendente</option>
                                        <option value="confirmado" <?= $app['status'] == 'confirmado' ? 'selected' : '' ?>>✅ Confirmado</option>
                                        <option value="atendimento" <?= $app['status'] == 'atendimento' ? 'selected' : '' ?>>🛠️ Em atendimento</option>
                                        <option value="pago" <?= $app['status'] == 'pago' ? 'selected' : '' ?>>💰 Concluído / Pago</option>
                                        <option value="cancelado" <?= $app['status'] == 'cancelado' ? 'selected' : '' ?>>❌ Cancelado</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Valor Fechado (R$)</label>
                                    <input type="text" name="price" value="<?= number_format($app['price'] ?? 0, 2, ',', '.') ?>" placeholder="0,00" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-bold text-slate-700 shadow-sm transition-all">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Observações da Equipe</label>
                                <textarea name="notes" rows="4" placeholder="Ex: Cliente pediu para avisar 30min antes de chegar..." class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-medium text-slate-700 shadow-sm transition-all resize-none"><?= htmlspecialchars($app['notes'] ?? '') ?></textarea>
                            </div>

                            <button type="submit" class="w-full py-3.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-500/30 flex items-center justify-center gap-2">
                                <i class="ph-bold ph-floppy-disk text-lg"></i> Salvar Alterações
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </main>
</body>
</html>
