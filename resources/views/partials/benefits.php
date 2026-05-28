<!-- Benefícios dos Serviços -->
<section class="beneficios section-padding section-compact" id="servicos" aria-labelledby="beneficios-heading">
    <div class="container">
        <header class="section-header text-center">
            <p class="subtitle">Por que escolher?</p>
            <h2 id="beneficios-heading">Benefícios que vão além da limpeza</h2>
        </header>

        <ul class="benefits-grid" role="list">
            <?php foreach ($benefits as $benefit): ?>
            <li class="benefit-card">
                <div class="icon-wrapper" aria-hidden="true">
                    <i class="ph <?= htmlspecialchars($benefit['icon']) ?>"></i>
                </div>
                <h3><?= htmlspecialchars($benefit['title']) ?></h3>
                <p><?= htmlspecialchars($benefit['desc']) ?></p>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
