<!-- Depoimentos -->
<section class="depoimentos section-padding bg-light" id="depoimentos" aria-labelledby="depoimentos-heading">
    <div class="container">
        <header class="section-header text-center">
            <p class="subtitle">Depoimentos</p>
            <h2 id="depoimentos-heading">O que nossos clientes dizem</h2>
        </header>

        <div class="testimonial-carousel" role="region" aria-label="Carrossel de depoimentos">
            <ul class="testimonial-track" role="list">
                <?php foreach ($testimonials as $t): ?>
                <li class="testimonial-card">
                    <div class="stars" aria-label="Avaliação: 5 estrelas">
                        <i class="ph-fill ph-star" aria-hidden="true"></i>
                        <i class="ph-fill ph-star" aria-hidden="true"></i>
                        <i class="ph-fill ph-star" aria-hidden="true"></i>
                        <i class="ph-fill ph-star" aria-hidden="true"></i>
                        <i class="ph-fill ph-star" aria-hidden="true"></i>
                    </div>
                    <blockquote class="quote"><?= htmlspecialchars($t['quote']) ?></blockquote>
                    <footer class="author">
                        <div class="avatar" aria-hidden="true"><?= htmlspecialchars($t['initial']) ?></div>
                        <div class="author-info">
                            <cite><strong><?= htmlspecialchars($t['author']) ?></strong></cite>
                            <span><?= htmlspecialchars($t['city']) ?></span>
                        </div>
                    </footer>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>
