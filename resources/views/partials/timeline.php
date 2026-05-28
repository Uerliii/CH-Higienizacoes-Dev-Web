<!-- Como Funciona -->
<section class="como-funciona section-padding" id="como-funciona" aria-labelledby="como-funciona-heading">
    <div class="container">
        <header class="section-header text-center">
            <p class="subtitle">O Processo</p>
            <h2 id="como-funciona-heading">Como a mágica acontece</h2>
        </header>

        <div class="timeline-container" aria-label="Etapas do processo">
            <div class="timeline-line" aria-hidden="true"></div>
            <ol role="list" style="list-style: none; padding: 0; margin: 0;">
                <?php foreach ($steps as $step): ?>
                <li class="timeline-item">
                    <div class="timeline-dot" aria-hidden="true"><i class="ph <?= htmlspecialchars($step['icon']) ?>"></i></div>
                    <div class="timeline-content">
                        <h3><?= htmlspecialchars($step['title']) ?></h3>
                        <p><?= htmlspecialchars($step['desc']) ?></p>
                    </div>
                </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>
</section>
