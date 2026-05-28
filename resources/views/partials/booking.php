<!-- Agendamento Interativo -->
<section class="agendamento-section section-padding bg-dark text-white" id="agendamento"
    aria-labelledby="agendamento-heading">
    <div class="container agendamento-container">
        <div class="agendamento-info">
            <h2 id="agendamento-heading">Pronto para renovar seu ambiente?</h2>
            <p>Agende agora mesmo de forma rápida e 100% online. Escolha o serviço, a data ideal e nós cuidamos
                do resto.</p>

            <ul class="diferenciais-list mt-4" aria-label="Diferenciais do serviço">
                <?php foreach ($diferenciais as $item): ?>
                <li><i class="ph-fill ph-check-circle text-primary" aria-hidden="true"></i> <?= htmlspecialchars($item) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="agendamento-form-wrapper">
            <nav class="wizard-progress" aria-label="Etapas do agendamento">
                <div class="progress-bar" id="wizard-progress-bar" role="progressbar" aria-valuenow="1"
                    aria-valuemin="1" aria-valuemax="3"></div>
                <ol>
                    <li class="step-indicator active" data-step="1" aria-current="step">1</li>
                    <li class="step-indicator" data-step="2">2</li>
                    <li class="step-indicator" data-step="3">3</li>
                </ol>
            </nav>

            <form id="booking-form" class="wizard-form" novalidate aria-label="Formulário de agendamento">
                <!-- Token CSRF para a API PHP -->
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">

                <!-- Passo 1: Serviço -->
                <fieldset class="wizard-step active" id="step-1">
                    <legend>
                        <h3>O que deseja higienizar?</h3>
                    </legend>
                    <div class="service-selector" role="radiogroup" aria-required="true">
                        <?php foreach ($services as $svc): ?>
                        <label class="service-option">
                            <input type="radio" name="service" value="<?= htmlspecialchars($svc['value']) ?>" <?= $svc['value'] === 'sofa' ? 'required' : '' ?>>
                            <div class="option-card">
                                <i class="ph <?= htmlspecialchars($svc['icon']) ?>" aria-hidden="true"></i>
                                <span><?= htmlspecialchars($svc['label']) ?></span>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-actions right">
                        <button type="button" class="btn btn-primary btn-next">
                            Próximo <i class="ph ph-arrow-right" aria-hidden="true"></i>
                        </button>
                    </div>
                </fieldset>

                <!-- Passo 2: Data e Hora -->
                <fieldset class="wizard-step" id="step-2">
                    <legend>
                        <h3>Escolha o melhor dia</h3>
                    </legend>
                    <div class="date-time-selector">
                        <div class="input-group">
                            <label for="data">Data:</label>
                            <input type="date" id="data" name="data" required>
                        </div>

                        <label class="input-group">Horário preferencial:</label>
                        <div class="time-selector" role="radiogroup" aria-label="Horário preferencial">
                            <label class="time-option">
                                <input type="radio" name="horario" value="manha" required>
                                <span class="time-btn">Manhã (08h - 12h)</span>
                            </label>
                            <label class="time-option">
                                <input type="radio" name="horario" value="tarde">
                                <span class="time-btn">Tarde (13h - 18h)</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-actions space-between">
                        <button type="button" class="btn btn-outline-light btn-prev">
                            <i class="ph ph-arrow-left" aria-hidden="true"></i> Voltar
                        </button>
                        <button type="button" class="btn btn-primary btn-next">
                            Próximo <i class="ph ph-arrow-right" aria-hidden="true"></i>
                        </button>
                    </div>
                </fieldset>

                <!-- Passo 3: Dados -->
                <fieldset class="wizard-step" id="step-3">
                    <legend>
                        <h3>Seus Dados:</h3>
                    </legend>
                    <div class="user-data-form">
                        <div class="input-group input-animated">
                            <input type="text" id="nome" name="nome" placeholder=" " required autocomplete="name">
                            <label for="nome">Nome Completo</label>
                            <div class="input-border"></div>
                        </div>
                        <div class="input-group input-animated">
                            <input type="tel" id="whatsapp" name="whatsapp" placeholder=" " required autocomplete="tel">
                            <label for="whatsapp">WhatsApp</label>
                            <div class="input-border"></div>
                        </div>
                        <div class="input-group input-animated">
                            <input type="text" id="cidade" name="cidade" placeholder=" " required autocomplete="address-level2">
                            <label for="cidade">Cidade / Bairro</label>
                            <div class="input-border"></div>
                        </div>
                    </div>
                    <div class="form-actions space-between">
                        <button type="button" class="btn btn-outline-light btn-prev">
                            <i class="ph ph-arrow-left" aria-hidden="true"></i> Voltar
                        </button>
                        <button type="submit" class="btn btn-primary btn-glow btn-submit">
                            <span class="btn-text">Confirmar Agendamento</span>
                            <i class="ph ph-check" aria-hidden="true"></i>
                        </button>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</section>
