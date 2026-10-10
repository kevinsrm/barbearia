<?php

namespace App\Http\Controllers;

use App\Models\ProdutoCategoria;
use App\Models\ServicoCategoria;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Gerencia as categorias de serviços e de produtos (mesma lógica, dois tipos).
 */
class CategoriaController extends Controller
{
    public function store(Request $request, string $tipo)
    {
        $modelo = $this->modelo($tipo);

        $dados = $request->validate([
            'nome' => 'required|string|min:2|max:120',
            'descricao' => 'nullable|string|max:255',
            'ordem' => 'nullable|integer|min:0|max:9999',
        ]);

        $modelo::create([
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'ordem' => $dados['ordem'] ?? 0,
            'ativo' => true,
        ]);

        return back()->with('success', 'Categoria criada!');
    }

    public function update(Request $request, string $tipo, int $id)
    {
        $modelo = $this->modelo($tipo);
        $categoria = $modelo::findOrFail($id);

        $dados = $request->validate([
            'nome' => 'required|string|min:2|max:120',
            'descricao' => 'nullable|string|max:255',
            'ordem' => 'nullable|integer|min:0|max:9999',
        ]);

        $categoria->update($dados + ['ativo' => $request->boolean('ativo', $categoria->ativo)]);

        return back()->with('success', 'Categoria atualizada!');
    }

    public function destroy(string $tipo, int $id)
    {
        $modelo = $this->modelo($tipo);
        $categoria = $modelo::findOrFail($id);
        $relacao = $tipo === 'servico' ? 'servicos' : 'produtos';

        if ($categoria->{$relacao}()->exists()) {
            $categoria->update(['ativo' => false]);

            return back()->with('error', 'Categoria possui itens vinculados e foi apenas desativada.');
        }

        $categoria->delete();

        return back()->with('success', 'Categoria removida.');
    }

    protected function modelo(string $tipo): string
    {
        return match ($tipo) {
            'servico' => ServicoCategoria::class,
            'produto' => ProdutoCategoria::class,
            default => abort(404, 'Tipo de categoria inválido.'),
        };
    }
}
