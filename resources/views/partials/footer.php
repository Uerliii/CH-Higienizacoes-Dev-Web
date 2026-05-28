<!-- Footer -->
<footer class="footer">
    <div class="container footer-container">
        <div class="footer-brand">
            <a href="#" class="logo" aria-label="CH Higienizações — página inicial">
                <span class="logo-text">CH<span class="text-primary"> Higienizações</span></span>
            </a>
            <p>Especialistas em renovar seus estofados com tecnologia e excelência. Atendimento premium na Bahia.
            </p>

            <nav class="social-links" aria-label="Redes sociais">
                <a href="<?= htmlspecialchars(COMPANY_INSTAGRAM) ?>" target="_blank"
                    rel="noopener noreferrer" class="social-icon" aria-label="Instagram">
                    <i class="ph ph-instagram-logo" aria-hidden="true"></i>
                </a>

                <a href="https://wa.me/<?= COMPANY_WHATSAPP ?>" target="_blank"
                    rel="noopener noreferrer" class="social-icon" aria-label="WhatsApp">
                    <i class="ph ph-whatsapp-logo" aria-hidden="true"></i>
                </a>
            </nav>
        </div>

        <nav class="footer-links" aria-label="Links rápidos">
            <h3>Links Rápidos</h3>
            <ul>
                <li><a href="#home">Início</a></li>
                <li><a href="#sobre-nos">Sobre Nós</a></li>
                <li><a href="#como-funciona">Como Funciona</a></li>
                <li><a href="#antes-depois">Resultados</a></li>
                <li><a href="#agendamento">Agendar</a></li>
            </ul>
        </nav>

        <address class="footer-contact">
            <h3>Contato</h3>
            <ul>
                <li>
                    <a href="https://wa.me/<?= COMPANY_WHATSAPP ?>" target="_blank" rel="noopener noreferrer"
                        style="color: inherit; text-decoration: none; display: flex; align-items: center; gap: 0.75rem;">
                        <i class="ph ph-whatsapp-logo text-primary" aria-hidden="true"></i><?= COMPANY_PHONE ?>
                    </a>
                </li>
                <li><i class="ph ph-envelope text-primary" aria-hidden="true"></i> <?= COMPANY_EMAIL ?></li>
                <li><i class="ph ph-map-pin text-primary" aria-hidden="true"></i><?= COMPANY_CITY ?></li>
            </ul>
        </address>
    </div>
    <div class="footer-bottom text-center">
        <small>&copy; <?= date('Y') ?> CH Higienizações. Todos os direitos reservados.</small>
    </div>
</footer>
