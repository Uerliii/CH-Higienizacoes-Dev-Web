<?php

namespace Controllers;

/**
 * HomeController — Renderiza a página principal.
 * Equivalente ao Express static serve do index.html original.
 */
class HomeController
{
    public function index(): void
    {
        // Dados passados para a view (equivalentes ao HTML estático original)
        $data = [
            'title'       => 'CH Higienizações | Limpeza Profissional de Estofados',
            'description' => 'Especialistas em higienização profissional de estofados, sofás, colchões e interiores automotivos na Bahia. Agende agora e renove seu ambiente!',

            // Serviços para o Step 1 do wizard (mantendo os mesmos 4 do HTML)
            'services' => [
                ['value' => 'sofa',    'label' => 'Sofá',         'icon' => 'ph-armchair'],
                ['value' => 'colchao', 'label' => 'Colchão',      'icon' => 'ph-bed'],
                ['value' => 'carro',   'label' => 'Carro (Bancos)','icon' => 'ph-car'],
                ['value' => 'tapete',  'label' => 'Tapete',        'icon' => 'ph-rug'],
            ],

            // Benefícios (Step benefícios section — mantendo os 4 cards originais)
            'benefits' => [
                ['icon' => 'ph-shield-plus',   'title' => 'Eliminação de Ácaros',   'desc' => 'Removemos 99,9% dos ácaros e bactérias causadores de alergias respiratórias.'],
                ['icon' => 'ph-sparkle',        'title' => 'Estética Renovada',     'desc' => 'Devolvemos a cor original do tecido, removendo manchas difíceis e encardidos.'],
                ['icon' => 'ph-wind',           'title' => 'Fim dos Maus Odores',   'desc' => 'Neutralizamos odores de urina pet, suor, mofo e cigarro com produtos específicos.'],
                ['icon' => 'ph-hourglass-high', 'title' => 'Aumento da Vida Útil', 'desc' => 'A higienização periódica evita o apodrecimento e desgaste precoce das fibras.'],
            ],

            // Timeline — Como Funciona (4 etapas originais)
            'steps' => [
                ['icon' => 'ph-magnifying-glass', 'title' => '1. Avaliação do Tecido',    'desc' => 'Identificamos o tipo de tecido e as manchas para aplicar o produto correto, garantindo segurança e eficácia.'],
                ['icon' => 'ph-drop',              'title' => '2. Aplicação do Produto',   'desc' => 'Utilizamos produtos biodegradáveis e bactericidas de alta performance que dissolvem a sujeira profunda.'],
                ['icon' => 'ph-wind',              'title' => '3. Extração a Vácuo',       'desc' => 'Nossa máquina de extração remove simultaneamente a água, o produto e toda a sujidade extraída do estofado.'],
                ['icon' => 'ph-check-circle',      'title' => '4. Secagem e Finalização',  'desc' => 'Processo de secagem acelerada. Em poucas horas seu móvel está pronto para uso, renovado e cheiroso.'],
            ],

            // Depoimentos (mantendo os 3 originais)
            'testimonials' => [
                ['quote' => '"Meu sofá parecia não ter mais salvação, ia até comprar outro. A equipe da CH fez mágica, ficou parecendo que acabou de sair da loja. Muito caprichosos!"', 'author' => 'Mariana Silva',  'city' => 'Salvador, BA',           'initial' => 'M'],
                ['quote' => '"Atendimento impecável desde o agendamento até a execução. O cheiro de limpeza que ficou em casa é maravilhoso e a secagem foi super rápida."',              'author' => 'Roberto Alves',  'city' => 'Lauro de Freitas, BA',    'initial' => 'R'],
                ['quote' => '"Fiz a higienização dos bancos do meu carro que estavam bem encardidos. Fiquei impressionado com a quantidade de sujeira que saiu. Recomendo de olhos fechados."', 'author' => 'Carlos Mendes', 'city' => 'Camaçari, BA', 'initial' => 'C'],
            ],

            // Diferenciais da seção de agendamento
            'diferenciais' => [
                'Atendimento em domicílio',
                'Produtos biodegradáveis',
                'Secagem rápida',
                'Profissionais qualificados',
            ],

            'csrf' => csrfToken(),
        ];

        view('layouts.app', $data);
    }
}
