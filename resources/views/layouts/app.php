<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($description) ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Icons (Phosphor Icons) -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- CSS Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">

    <!-- JS Flatpickr PT-BR -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/pt.js"></script>
</head>

<body>
    <!-- Preloader -->
    <div class="preloader" role="status" aria-label="Carregando">
        <div class="spinner"></div>
    </div>

    <!-- Navigation -->
    <?php require VIEWS_PATH . '/partials/navbar.php'; ?>

    <main>
        <!-- Hero Section -->
        <?php require VIEWS_PATH . '/partials/hero.php'; ?>

        <!-- Sobre Nós -->
        <?php require VIEWS_PATH . '/partials/about.php'; ?>

        <!-- Benefícios dos Serviços -->
        <?php require VIEWS_PATH . '/partials/benefits.php'; ?>

        <!-- Como Funciona -->
        <?php require VIEWS_PATH . '/partials/timeline.php'; ?>

        <!-- Prova Visual (Antes e Depois) -->
        <?php require VIEWS_PATH . '/partials/before-after.php'; ?>

        <!-- Agendamento Interativo -->
        <?php require VIEWS_PATH . '/partials/booking.php'; ?>

        <!-- Depoimentos -->
        <?php require VIEWS_PATH . '/partials/testimonials.php'; ?>
    </main>

    <!-- Footer -->
    <?php require VIEWS_PATH . '/partials/footer.php'; ?>

    <!-- Chatbot CTA Fix -->
    <?php require VIEWS_PATH . '/partials/chatbot.php'; ?>

    <!-- Modal de Sucesso -->
    <?php require VIEWS_PATH . '/partials/modal.php'; ?>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

    <!-- CSRF token disponível para o JS -->
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf) ?>">

    <script src="assets/js/main.js?v=<?= time() ?>"></script>
</body>

</html>
