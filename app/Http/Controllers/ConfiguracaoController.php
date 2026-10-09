<?php

namespace App\Http\Controllers;

use App\Support\EnvEditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ConfiguracaoController extends Controller
{
    /**
     * Tela de configurações do sistema: os dados são gravados no .env,
     * ou seja, sobrevivem a atualizações de código.
     */
    public function index()
    {
        return view('configuracoes.index', [
            'configuracoes' => [
                'BARBEARIA_NOME' => config('barbearia.nome'),
                'BARBEARIA_TELEFONE' => config('barbearia.telefone'),
                'BARBEARIA_EMAIL' => config('barbearia.email'),
                'BARBEARIA_ENDERECO' => config('barbearia.endereco'),
                'BARBEARIA_CIDADE' => config('barbearia.cidade'),
                'BARBEARIA_INSTAGRAM' => config('barbearia.instagram'),
                'BARBEARIA_WHATSAPP' => config('barbearia.whatsapp'),
                'AGENDA_ABERTURA' => config('barbearia.agenda.abertura'),
                'AGENDA_FECHAMENTO' => config('barbearia.agenda.fechamento'),
                'AGENDA_INTERVALO' => config('barbearia.agenda.intervalo'),
                'AGENDA_DIAS_FECHADOS' => implode(',', array_map('intval', (array) config('barbearia.agenda.dias_fechados'))),
                'AGENDA_ANTECEDENCIA_MINIMA' => config('barbearia.agenda.antecedencia_minima'),
                'COMISSAO_PADRAO' => config('barbearia.comissao_padrao'),
                'COMISSAO_PRODUTOS' => config('barbearia.comissao_sobre_produtos') ? 'true' : 'false',
                'BLOQUEAR_VENDA_SEM_ESTOQUE' => config('barbearia.bloquear_venda_sem_estoque') ? 'true' : 'false',
                'PRAZO_FIADO_DIAS' => config('barbearia.prazo_fiado_dias'),
                'PAGINACAO_POR_PAGINA' => config('barbearia.paginacao'),
            ],
            'envGravavel' => EnvEditor::isWritable(),
        ]);
    }

    public function update(Request $request)
    {
        $dados = $request->validate([
            'BARBEARIA_NOME' => 'required|string|max:120',
            'BARBEARIA_TELEFONE' => 'nullable|string|max:40',
            'BARBEARIA_EMAIL' => 'nullable|email|max:180',
            'BARBEARIA_ENDERECO' => 'nullable|string|max:180',
            'BARBEARIA_CIDADE' => 'nullable|string|max:120',
            'BARBEARIA_INSTAGRAM' => 'nullable|string|max:120',
            'BARBEARIA_WHATSAPP' => 'nullable|string|max:40',
            'AGENDA_ABERTURA' => ['required', 'date_format:H:i'],
            'AGENDA_FECHAMENTO' => ['required', 'date_format:H:i', 'after:AGENDA_ABERTURA'],
            'AGENDA_INTERVALO' => 'required|integer|in:10,15,20,30,45,60',
            'AGENDA_DIAS_FECHADOS' => 'nullable|string|max:20',
            'AGENDA_ANTECEDENCIA_MINIMA' => 'nullable|integer|min:0|max:1440',
            'COMISSAO_PADRAO' => 'nullable|numeric|min:0|max:100',
            'COMISSAO_PRODUTOS' => 'nullable|boolean',
            'BLOQUEAR_VENDA_SEM_ESTOQUE' => 'nullable|boolean',
            'PRAZO_FIADO_DIAS' => 'nullable|integer|min:0|max:365',
            'PAGINACAO_POR_PAGINA' => 'nullable|integer|min:5|max:100',
        ]);

        $dados['COMISSAO_PRODUTOS'] = $request->boolean('COMISSAO_PRODUTOS') ? 'true' : 'false';
        $dados['BLOQUEAR_VENDA_SEM_ESTOQUE'] = $request->boolean('BLOQUEAR_VENDA_SEM_ESTOQUE') ? 'true' : 'false';
        $dados['APP_NAME'] = $dados['BARBEARIA_NOME'];
        $dados['APP_URL'] = $request->string('APP_URL')->toString() ?: (string) config('app.url');

        if (! EnvEditor::setValues($dados)) {
            return back()->with('error', 'Não foi possível gravar o arquivo .env. Verifique as permissões de escrita.');
        }

        Artisan::call('config:clear');

        return back()->with('success', 'Configurações salvas! Elas já valem para as próximas páginas.');
    }
}
