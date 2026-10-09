<?php

/*
|--------------------------------------------------------------------------
| Configurações do sistema de barbearia
|--------------------------------------------------------------------------
| Tudo que o dono da barbearia costuma querer ajustar fica aqui (ou no .env,
| que tem prioridade). Nada precisa ser alterado no código.
*/

return [

    'nome' => env('BARBEARIA_NOME', env('APP_NAME', 'Barbearia')),
    'telefone' => env('BARBEARIA_TELEFONE', '(98) 99999-0000'),
    'email' => env('BARBEARIA_EMAIL', 'contato@barbearia.com'),
    'endereco' => env('BARBEARIA_ENDERECO', 'Rua Exemplo, 123 - Centro'),
    'cidade' => env('BARBEARIA_CIDADE', 'São Luís - MA'),
    'instagram' => env('BARBEARIA_INSTAGRAM', ''),
    'whatsapp' => env('BARBEARIA_WHATSAPP', ''),

    /* Agenda / agendamento online */
    'agenda' => [
        'abertura' => env('AGENDA_ABERTURA', '09:00'),
        'fechamento' => env('AGENDA_FECHAMENTO', '20:00'),
        'intervalo' => (int) env('AGENDA_INTERVALO', 30),          // minutos entre horários
        // 0 = domingo, 1 = segunda ... 6 = sábado. Ex.: "0" fecha aos domingos.
        'dias_fechados' => array_values(array_filter(
            array_map('intval', array_filter(
                explode(',', (string) env('AGENDA_DIAS_FECHADOS', '0')),
                fn ($valor) => trim($valor) !== ''
            )),
            fn ($dia) => $dia >= 0 && $dia <= 6
        )),
        'antecedencia_minima' => (int) env('AGENDA_ANTECEDENCIA_MINIMA', 60), // minutos antes do horário
        'dias_visiveis' => (int) env('AGENDA_DIAS_VISIVEIS', 30),
    ],

    /* Comissão padrão (em %) quando o profissional/serviço não define uma própria */
    'comissao_padrao' => (float) env('COMISSAO_PADRAO', 40),

    /* Se true, produtos vendidos também geram comissão para o profissional */
    'comissao_sobre_produtos' => (bool) env('COMISSAO_PRODUTOS', false),

    /*
    | Se true, o sistema não deixa vender mais do que há no estoque (evita saldo
    | negativo). Quem não controla estoque pode desligar em Configurações.
    */
    'bloquear_venda_sem_estoque' => (bool) env('BLOQUEAR_VENDA_SEM_ESTOQUE', true),

    /* Prazo (dias) para receber vendas feitas no "fiado" */
    'prazo_fiado_dias' => (int) env('PRAZO_FIADO_DIAS', 30),

    /* Quantos registros por página nas listagens do painel */
    'paginacao' => (int) env('PAGINACAO_POR_PAGINA', 15),

    /*
    | Permissões do painel. Cada habilidade lista os papéis que podem usá-la.
    | O papel "admin" sempre tem acesso a tudo (Gate::before).
    */
    'permissoes' => [
        'gerenciar-pessoas' => ['admin', 'gerente', 'recepcao'],
        'gerenciar-servicos' => ['admin', 'gerente'],
        'gerenciar-produtos' => ['admin', 'gerente'],
        'gerenciar-agendamentos' => ['admin', 'gerente', 'recepcao'],
        'gerenciar-vendas' => ['admin', 'gerente', 'recepcao'],
        'gerenciar-compras' => ['admin', 'gerente'],
        'gerenciar-financeiro' => ['admin', 'gerente'],
        'ver-relatorios' => ['admin', 'gerente'],
        'gerenciar-usuarios' => ['admin'],
        'acesso-barbeiro' => ['admin', 'gerente', 'barbeiro'],
    ],
];
