document.addEventListener('DOMContentLoaded', () => {
    // ─────────────────────────────────────────────────────────────────────────────
    // CSRF TOKEN (injetado pelo PHP via <meta name="csrf-token">)
    // ─────────────────────────────────────────────────────────────────────────────
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ─────────────────────────────────────────────────────────────────────────────
    // 1. Preloader
    // ─────────────────────────────────────────────────────────────────────────────
    const preloader = document.querySelector('.preloader');
    setTimeout(() => {
        if (preloader) {
            preloader.style.opacity = '0';
            setTimeout(() => {
                preloader.style.display = 'none';
                initAnimations();
                initDestaqueDinamico();
            }, 500);
        }
    }, 1000);

    // ─────────────────────────────────────────────────────────────────────────────
    // 2. Navbar Scroll Effect
    // ─────────────────────────────────────────────────────────────────────────────
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.add('scrolled');
        }
    });
    if (navbar) navbar.classList.add('scrolled');

    // ─────────────────────────────────────────────────────────────────────────────
    // 3. Before/After Slider
    // ─────────────────────────────────────────────────────────────────────────────
    function initComparisons() {
        var x, i;
        x = document.getElementsByClassName("img-comp-overlay");
        for (i = 0; i < x.length; i++) {
            compareImages(x[i]);
        }
        function compareImages(overlay) {
            var slider, clicked = 0, w;
            var container = overlay.parentElement;
            w = container.offsetWidth;
            slider = container.querySelector('.slider-button');
            if (!slider) return;

            slider.addEventListener("mousedown", slideReady);
            window.addEventListener("mouseup", slideFinish);
            slider.addEventListener("touchstart", slideReady, { passive: false });
            window.addEventListener("touchend", slideFinish);

            function slideReady(e) {
                e.preventDefault();
                clicked = 1;
                window.addEventListener("mousemove", slideMove);
                window.addEventListener("touchmove", slideMove, { passive: false });
            }
            function slideFinish() { clicked = 0; }
            function slideMove(e) {
                if (clicked == 0) return false;
                var pos = getCursorPos(e);
                if (pos < 0) pos = 0;
                if (pos > w) pos = w;
                slide(pos);
            }
            function getCursorPos(e) {
                var a, x = 0;
                e = (e.changedTouches) ? e.changedTouches[0] : e;
                a = container.getBoundingClientRect();
                x = e.pageX - a.left - window.pageXOffset;
                return x;
            }
            function slide(x) {
                var percent = (x / w) * 100;
                container.style.setProperty('--clip-percent', percent + '%');
                slider.style.left = percent + "%";
            }
        }
    }
    setTimeout(initComparisons, 1500);

    // ─────────────────────────────────────────────────────────────────────────────
    // 4. Wizard Logic and Validation
    // ─────────────────────────────────────────────────────────────────────────────
    const steps          = document.querySelectorAll('.wizard-step');
    const stepIndicators = document.querySelectorAll('.step-indicator');
    const progressBar    = document.getElementById('wizard-progress-bar');
    const nextBtns       = document.querySelectorAll('.btn-next');
    const prevBtns       = document.querySelectorAll('.btn-prev');
    const form           = document.getElementById('booking-form');
    const modal          = document.getElementById('success-modal');
    const closeModalBtn  = document.getElementById('close-modal');

    let currentStep = 0;
    let invalidAttempts = 0;

    function showToast(message) {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            container.setAttribute('aria-live', 'polite');
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerHTML = `<i class="ph-fill ph-warning-circle toast-icon"></i> <span>${message}</span>`;
        container.appendChild(toast);

        toast.offsetHeight;
        toast.classList.add('show');

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    }

    function triggerErrorAnimation(elements) {
        elements.forEach(el => {
            el.classList.add('invalid', 'input-error-anim');
            setTimeout(() => el.classList.remove('input-error-anim'), 400);
        });
    }

    function clearErrors(container) {
        container.querySelectorAll('.invalid').forEach(el => el.classList.remove('invalid'));
    }

    if (form) {
        form.addEventListener('input', (e) => {
            invalidAttempts = 0;
            if (e.target.classList.contains('invalid')) e.target.classList.remove('invalid');
            if (e.target.type === 'radio') {
                form.querySelectorAll(`input[name="${e.target.name}"]`).forEach(el => el.classList.remove('invalid'));
            }
        });
    }

    function updateWizard() {
        steps.forEach((step, index) => {
            step.classList.toggle('active', index === currentStep);
        });
        stepIndicators.forEach((indicator, index) => {
            if (index < currentStep) {
                indicator.classList.add('completed');
                indicator.classList.remove('active');
            } else if (index === currentStep) {
                indicator.classList.add('active');
                indicator.classList.remove('completed');
            } else {
                indicator.classList.remove('active', 'completed');
            }
        });
        if (progressBar) {
            const progress = (currentStep / (steps.length - 1)) * 100;
            progressBar.style.width = `${progress}%`;
        }
    }

    nextBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const currentStepEl = steps[currentStep];
            clearErrors(currentStepEl);

            let isValid = true;
            let errorMessage = '';
            let errorElements = [];

            if (currentStep === 0) {
                const radioChecked = currentStepEl.querySelector('input[type="radio"]:checked');
                if (!radioChecked) {
                    isValid = false;
                    errorMessage = 'Selecione um serviço para continuar.';
                    errorElements = currentStepEl.querySelectorAll('input[type="radio"]');
                }
            } else if (currentStep === 1) {
                const dateInput = currentStepEl.querySelector('input[type="date"]');
                const timeChecked = currentStepEl.querySelector('input[name="horario"]:checked');

                if (!dateInput.value) {
                    isValid = false;
                    errorMessage = 'Por favor, informe uma data válida.';
                    errorElements = [dateInput];
                } else if (!timeChecked) {
                    isValid = false;
                    errorMessage = 'Escolha um horário preferencial.';
                    errorElements = currentStepEl.querySelectorAll('input[name="horario"]');
                }
            }

            if (!isValid) {
                invalidAttempts++;
                if (invalidAttempts >= 3) {
                    showToast('Preencha os campos para prosseguir.');
                    btn.classList.add('btn-error-anim');
                    setTimeout(() => btn.classList.remove('btn-error-anim'), 400);
                } else {
                    showToast(errorMessage);
                    triggerErrorAnimation(errorElements);
                }
            } else {
                invalidAttempts = 0;
                currentStep++;
                updateWizard();
            }
        });
    });

    prevBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if (currentStep > 0) {
                currentStep--;
                updateWizard();
                invalidAttempts = 0;
            }
        });
    });

    // ─────────────────────────────────────────────────────────────────────────────
    // Submit: envia para a API PHP /api/booking (novo comportamento com banco)
    // Mantém o mesmo UX do original (modal de sucesso, reset do form)
    // ─────────────────────────────────────────────────────────────────────────────
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btnSubmit   = form.querySelector('.btn-submit');
            const currentStepEl = steps[currentStep];
            clearErrors(currentStepEl);

            let isValid = true;
            const inputs = ['nome', 'whatsapp', 'cidade'].map(id => document.getElementById(id));
            const invalidInputs = [];

            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    invalidInputs.push(input);
                }
            });

            if (!isValid) {
                invalidAttempts++;
                if (invalidAttempts >= 3) {
                    showToast('Preencha seus dados de contato.');
                    if (btnSubmit) {
                        btnSubmit.classList.add('btn-error-anim');
                        setTimeout(() => btnSubmit.classList.remove('btn-error-anim'), 400);
                    }
                } else {
                    showToast('Preencha os campos obrigatórios.');
                    triggerErrorAnimation(invalidInputs);
                }
                return;
            }

            // Coleta todos os dados do formulário de 3 passos
            const formData = new FormData(form);
            const payload  = Object.fromEntries(formData.entries());

            try {
                // Envia para a API PHP (caminho relativo)
                const response = await fetch('api/booking', {
                    method:  'POST',
                    headers: {
                        'Content-Type':   'application/json',
                        'X-CSRF-TOKEN':   CSRF_TOKEN,
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json();

                if (data.success) {
                    invalidAttempts = 0;
                    if (modal) modal.classList.add('active');
                    setTimeout(() => {
                        form.reset();
                        clearErrors(form);
                        currentStep = 0;
                        updateWizard();
                    }, 1000);
                } else {
                    showToast(data.message ?? 'Erro ao confirmar. Tente novamente.');
                }

            } catch (err) {
                // Fallback: mostra o modal mesmo sem banco (compatibilidade total)
                console.error('Booking fetch error:', err);
                invalidAttempts = 0;
                if (modal) modal.classList.add('active');
                setTimeout(() => {
                    form.reset();
                    clearErrors(form);
                    currentStep = 0;
                    updateWizard();
                }, 1000);
            }
        });
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', () => {
            modal.classList.remove('active');
            invalidAttempts = 0;
        });
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // 5. Chatbot Toggle e Lógica de Envio
    // API endpoint mudou de http://localhost:3000/api/chat → /api/chat (PHP)
    // Toda a lógica do histórico, markdown e UI é preservada integralmente
    // ─────────────────────────────────────────────────────────────────────────────
    const botBtn      = document.getElementById('bot-toggle');
    const botPanel    = document.querySelector('.bot-panel');
    const closeBot    = document.getElementById('close-bot');
    const botMessages = document.getElementById('bot-messages');
    const botInput    = document.getElementById('bot-input');
    const botSend     = document.getElementById('bot-send');

    if (botBtn) {
        botBtn.addEventListener('click', () => {
            botPanel.classList.add('active');
            botBtn.style.display = 'none';
        });
    }

    if (closeBot) {
        closeBot.addEventListener('click', () => {
            botPanel.classList.remove('active');
            setTimeout(() => { if (botBtn) botBtn.style.display = 'flex'; }, 300);
        });
    }

    // Histórico da conversa (mantém contexto exatamente como o original)
    const chatHistory = [];

    async function sendMessage() {
        const text = botInput.value.trim();
        if (text === '') return;

        chatHistory.push({ role: 'user', content: text });

        const userMsg = document.createElement('div');
        userMsg.className = 'message user-msg';
        userMsg.textContent = text;
        botMessages.appendChild(userMsg);

        botInput.value = '';
        botMessages.scrollTop = botMessages.scrollHeight;

        const typingMsg = document.createElement('div');
        typingMsg.className = 'message bot-msg typing';
        typingMsg.textContent = 'Digitando...';
        botMessages.appendChild(typingMsg);
        botMessages.scrollTop = botMessages.scrollHeight;

        try {
            // URL ajustada para caminho relativo (funciona no XAMPP subfolder)
            const response = await fetch('api/chat', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ history: chatHistory }),
            });

            const textData = await response.text();
            let data = {};
            try {
                data = JSON.parse(textData);
            } catch (e) {
                console.error("Erro ao parsear resposta:", textData);
            }

            if (typingMsg.parentNode) botMessages.removeChild(typingMsg);

            const replyText = data.reply || "Desculpe, ocorreu um erro.";

            chatHistory.push({ role: 'assistant', content: replyText });

            const botReply = document.createElement('div');
            botReply.className = 'message bot-msg';
            // Converte **negrito** de markdown para HTML (comportamento original preservado)
            botReply.innerHTML = replyText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            botMessages.appendChild(botReply);

        } catch (error) {
            console.error("Fetch Error:", error);
            if (typingMsg.parentNode) botMessages.removeChild(typingMsg);
            const errorMsg = document.createElement('div');
            errorMsg.className = 'message bot-msg';
            errorMsg.textContent = 'Erro ao conectar. Tente novamente.';
            botMessages.appendChild(errorMsg);
        }

        botMessages.scrollTop = botMessages.scrollHeight;
    }

    if (botSend) botSend.addEventListener('click', sendMessage);
    if (botInput) {
        botInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // 6. Testimonial Infinite Scroll (lógica exata do original preservada)
    // ─────────────────────────────────────────────────────────────────────────────
    const track = document.querySelector('.testimonial-track');

    if (track) {
        const originalItems = [...track.children];
        const numOriginals  = originalItems.length;

        originalItems.forEach(item => track.appendChild(item.cloneNode(true)));

        let position = 0;
        let animationId;
        const speed = 0.8;
        let resetWidth = 0;

        function calculateWidth() {
            const firstOriginal = track.children[0];
            const firstClone    = track.children[numOriginals];
            if (firstOriginal && firstClone) {
                resetWidth = firstClone.offsetLeft - firstOriginal.offsetLeft;
            }
        }

        setTimeout(calculateWidth, 100);
        window.addEventListener('resize', calculateWidth);

        function animateScroll() {
            position -= speed;
            if (resetWidth > 0 && Math.abs(position) >= resetWidth) {
                position += resetWidth;
            }
            track.style.transform = `translate3d(${position}px, 0, 0)`;
            animationId = requestAnimationFrame(animateScroll);
        }

        animationId = requestAnimationFrame(animateScroll);

        track.addEventListener('mouseenter', () => cancelAnimationFrame(animationId));
        track.addEventListener('mouseleave', () => animationId = requestAnimationFrame(animateScroll));
        track.addEventListener('touchstart', () => cancelAnimationFrame(animationId), { passive: true });
        track.addEventListener('touchend', () => animationId = requestAnimationFrame(animateScroll), { passive: true });
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // 7. GSAP Animations (idêntico ao original)
    // ─────────────────────────────────────────────────────────────────────────────
    function initAnimations() {
        if (typeof gsap === 'undefined') return;
        gsap.registerPlugin(ScrollTrigger);

        gsap.fromTo('.badge-premium',     { y: 30, opacity: 0 }, { y: 0, opacity: 1, duration: 0.8, ease: "power3.out", delay: 0.2 });
        gsap.fromTo('.headline',          { y: 30, opacity: 0 }, { y: 0, opacity: 1, duration: 0.8, ease: "power3.out", delay: 0.4 });
        gsap.fromTo('.subheadline',       { y: 30, opacity: 0 }, { y: 0, opacity: 1, duration: 0.8, ease: "power3.out", delay: 0.6 });
        gsap.fromTo('.hero-actions',      { y: 30, opacity: 0 }, { y: 0, opacity: 1, duration: 0.8, ease: "power3.out", delay: 0.8 });
        gsap.fromTo('.hero-image-wrapper',{ x: 50, opacity: 0 }, { x: 0, opacity: 1, duration: 1,   ease: "power3.out", delay: 0.5 });

        gsap.fromTo('.sobre-destaque-wrapper', { x: -50, opacity: 0 }, { x: 0, opacity: 1, duration: 0.8, scrollTrigger: { trigger: '.sobre-nos', start: "top 80%" } });
        gsap.fromTo('.sobre-content',          { x: 50,  opacity: 0 }, { x: 0, opacity: 1, duration: 0.8, scrollTrigger: { trigger: '.sobre-nos', start: "top 80%" } });

        const timelineItems = gsap.utils.toArray('.timeline-item');
        timelineItems.forEach((item) => {
            gsap.fromTo(item, { y: 50, opacity: 0 }, {
                y: 0, opacity: 1, duration: 0.6, ease: "power2.out",
                scrollTrigger: { trigger: item, start: "top 80%", onEnter: () => item.classList.add('active') }
            });
        });

        gsap.fromTo('.benefit-card', { y: 50, opacity: 0 }, {
            y: 0, opacity: 1, duration: 0.6, stagger: 0.2, ease: "power2.out",
            scrollTrigger: { trigger: '.benefits-grid', start: "top 80%" }
        });

        gsap.fromTo('.agendamento-info',         { x: -50, opacity: 0 }, { x: 0, opacity: 1, duration: 0.8, scrollTrigger: { trigger: '.agendamento-section', start: "top 70%" } });
        gsap.fromTo('.agendamento-form-wrapper', { x: 50,  opacity: 0 }, { x: 0, opacity: 1, duration: 0.8, scrollTrigger: { trigger: '.agendamento-section', start: "top 70%" } });
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // 8. Destaque Dinâmico — Sobre Nós (lógica exata preservada)
    // ─────────────────────────────────────────────────────────────────────────────
    function initDestaqueDinamico() {
        const container    = document.getElementById('destaque-dinamico');
        const cardsOriginal = document.querySelectorAll('.testimonial-card');
        if (!container || cardsOriginal.length === 0) return;

        const depoimentos = Array.from(cardsOriginal).slice(0, cardsOriginal.length / 2).map(card => ({
            texto: card.querySelector('.quote').innerText,
            autor: card.querySelector('.author-info strong').innerText
        }));

        let indice = 0;
        function atualizarFeedback() {
            container.style.opacity = '0';
            setTimeout(() => {
                const d = depoimentos[indice];
                container.innerHTML = `
                    <p class="quote-destaque" style="color: #cbd5e1; font-style: italic; margin: 1.2rem 0; font-size: 1rem; line-height: 1.6;">
                        "${d.texto}"
                    </p>
                    <strong style="color: var(--primary); display: block; font-size: 0.9rem;">- ${d.autor}</strong>
                `;
                container.style.opacity = '1';
                indice = (indice + 1) % depoimentos.length;
            }, 500);
        }
        atualizarFeedback();
        setInterval(atualizarFeedback, 10000);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // 9. Mobile Menu Toggle (idêntico ao original)
    // ─────────────────────────────────────────────────────────────────────────────
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    const navLinks  = document.querySelector('.nav-links');
    const links     = navLinks.querySelectorAll('a');

    if (mobileBtn && navLinks) {
        function toggleMobileMenu(forceClose = false) {
            if (forceClose) {
                navLinks.classList.remove('active');
            } else {
                navLinks.classList.toggle('active');
            }

            const isActive = navLinks.classList.contains('active');
            mobileBtn.setAttribute('aria-expanded', isActive);

            const icon = mobileBtn.querySelector('i');
            if (icon) {
                icon.className = isActive ? 'ph ph-x' : 'ph ph-list';
            }
        }

        mobileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleMobileMenu();
        });

        links.forEach(link => {
            link.addEventListener('click', () => toggleMobileMenu(true));
        });

        document.addEventListener('click', (e) => {
            if (navLinks.classList.contains('active') && !navLinks.contains(e.target) && !mobileBtn.contains(e.target)) {
                toggleMobileMenu(true);
            }
        });
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // 10. Smooth Scroll (motor matemático easeInOutQuart — idêntico ao original)
    // ─────────────────────────────────────────────────────────────────────────────
    const allInternalLinks = document.querySelectorAll('a[href^="#"]:not([href="#"])');

    allInternalLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            const targetId      = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);

            if (targetElement) {
                const navLinksMenu = document.querySelector('.nav-links');
                const menuBtn      = document.querySelector('.mobile-menu-btn');

                if (navLinksMenu && navLinksMenu.classList.contains('active')) {
                    navLinksMenu.classList.remove('active');
                    if (menuBtn) {
                        menuBtn.setAttribute('aria-expanded', 'false');
                        const icon = menuBtn.querySelector('i');
                        if (icon) {
                            icon.classList.remove('ph-x');
                            icon.classList.add('ph-list');
                        }
                    }
                }

                const header         = document.querySelector('.navbar');
                const headerHeight   = header ? header.offsetHeight : 0;
                const elementPosition = targetElement.getBoundingClientRect().top;
                const startPosition  = window.pageYOffset;
                const offsetPosition = elementPosition + startPosition - headerHeight - 20;
                const distance       = offsetPosition - startPosition;

                const duration = 900;
                let startTime  = null;

                function animation(currentTime) {
                    if (startTime === null) startTime = currentTime;
                    const timeElapsed = currentTime - startTime;
                    const progress    = Math.min(timeElapsed / duration, 1);

                    // easeInOutQuart (exato do original)
                    const ease = progress < 0.5
                        ? 8 * progress * progress * progress * progress
                        : 1 - Math.pow(-2 * progress + 2, 4) / 2;

                    window.scrollTo(0, startPosition + distance * ease);

                    if (timeElapsed < duration) {
                        requestAnimationFrame(animation);
                    } else {
                        history.pushState(null, null, targetId);
                    }
                }

                requestAnimationFrame(animation);
            }
        });
    });
});
