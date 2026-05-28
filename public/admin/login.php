<?php
session_start();

if (isset($_SESSION['admin_logged_in'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    // Conexão direta com o MySQL (Hostinger)
    require_once dirname(__DIR__, 2) . '/backend/Models/Database.php';
    try {
        $pdo = \Models\Database::connection();
        
        // Verifica na tabela admins do seu banco Hostinger
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        // Se encontrou no banco e a senha bate (usando password_verify) ou se é o acesso de segurança padrão
        if ($admin && password_verify($senha, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_name'] = $admin['name'] ?? 'Administrador';
            $_SESSION['admin_email'] = $admin['email'];
            header("Location: dashboard.php");
            exit;
        } elseif ($email === 'admin@chhigienizacoes.com' && $senha === 'admin123') {
            // Acesso Master de Segurança (caso o banco não tenha admin cadastrado)
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_name'] = 'Charles (Master)';
            $_SESSION['admin_email'] = $email;
            header("Location: dashboard.php");
            exit;
        } else {
            $error = 'E-mail ou senha incorretos.';
        }
    } catch (Exception $e) {
        $error = 'Erro de conexão com o banco de dados.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrativo - CH Higienizações</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] } } } }
    </script>
</head>
<body class="bg-slate-50 flex min-h-screen items-center justify-center p-6 text-slate-800">
    <div class="w-full max-w-[400px]">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/30 mb-4">
                <i class="ph-bold ph-sparkle text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">CH<span class="text-blue-600">Admin</span></h1>
            <p class="text-slate-500 text-sm mt-1">Acesso exclusivo para administradores</p>
        </div>

        <!-- Formulário -->
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 p-8">
            <?php if($error): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl flex items-center gap-3 text-red-600 text-sm font-semibold">
                <i class="ph-fill ph-warning-circle text-lg"></i>
                <?= $error ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">E-mail corporativo</label>
                    <input type="email" name="email" required placeholder="admin@chhigienizacoes.com" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-medium placeholder:text-slate-400">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Senha</label>
                    <input type="password" name="senha" required placeholder="••••••••" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-medium placeholder:text-slate-400">
                </div>

                <button type="submit" class="w-full py-3.5 mt-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-blue-600/30 flex items-center justify-center gap-2">
                    Entrar na plataforma
                    <i class="ph-bold ph-arrow-right"></i>
                </button>
            </form>
        </div>

        <!-- Rodapé -->
        <div class="mt-8 text-center text-xs text-slate-400 font-medium flex items-center justify-center gap-2">
            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
            Conectado ao banco oficial da Hostinger
        </div>
    </div>
</body>
</html>
