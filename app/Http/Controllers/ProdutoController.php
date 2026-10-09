<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\ProdutoCategoria;
use App\Services\EstoqueService;
use App\Support\Number;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProdutoController extends Controller
{
    public function __construct(protected EstoqueService $estoque) {}

    public function index(Request $request)
    {
        $produtos = Produto::query()
            ->with('categoria')
            ->when($request->filled('categoria'), fn ($q) => $q->where('categoria_id', $request->integer('categoria')))
            ->when($request->boolean('estoque_baixo'), fn ($q) => $q->estoqueBaixo())
            ->when($request->filled('busca'), function ($q) use ($request) {
                $busca = trim((string) $request->string('busca'));
                $q->where(fn ($sub) => $sub->where('nome', 'like', "%{$busca}%")->orWhere('codigo', 'like', "%{$busca}%"));
            })
            ->orderBy('nome')
            ->paginate(config('barbearia.paginacao', 15))
            ->withQueryString();

        return view('produtos.index', [
            'produtos' => $produtos,
            'categorias' => ProdutoCategoria::orderBy('ordem')->orderBy('nome')->get(),
            'totalEstoqueBaixo' => Produto::query()->ativos()->estoqueBaixo()->count(),
        ]);
    }

    public function create()
    {
        return view('produtos.create', [
            'produto' => new Produto(['unidade' => 'un', 'ativo' => true, 'estoque' => 0, 'estoque_minimo' => 0]),
            'categorias' => ProdutoCategoria::ativas()->orderBy('ordem')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Produto::create($this->validar($request));

        return redirect()->route('admin.produtos.index')->with('success', 'Produto cadastrado com sucesso!');
    }

    public function edit(Produto $produto)
    {
        return view('produtos.edit', [
            'produto' => $produto,
            'categorias' => ProdutoCategoria::orderBy('ordem')->get(),
        ]);
    }

    public function update(Request $request, Produto $produto)
    {
        $produto->update($this->validar($request));

        return redirect()->route('admin.produtos.index')->with('success', 'Produto atualizado!');
    }

    public function destroy(Produto $produto)
    {
        if ($produto->movimentacoes()->exists()) {
            $produto->update(['ativo' => false]);

            return back()->with('error', 'Produto possui movimentações de estoque e foi apenas desativado.');
        }

        $produto->delete();

        return back()->with('success', 'Produto removido.');
    }

    /** Ajuste manual de estoque (entrada, saída ou inventário). */
    public function movimentarEstoque(Request $request, Produto $produto)
    {
        Number::normalizarRequest($request, [], ['quantidade']);

        $dados = $request->validate([
            'tipo' => ['required', Rule::in(['entrada', 'saida', 'ajuste'])],
            'quantidade' => 'required|integer|min:0|max:99999',
            'observacoes' => 'nullable|string|max:180',
        ], [], ['quantidade' => 'quantidade']);

        $this->estoque->movimentar($produto, $dados['tipo'], (int) $dados['quantidade'], [
            'observacoes' => $dados['observacoes'] ?? 'Ajuste manual',
        ]);

        return back()->with('success', 'Estoque de "'.$produto->nome.'" atualizado.');
    }

    protected function validar(Request $request): array
    {
        $produto = $request->route('produto');

        Number::normalizarRequest($request, ['preco_custo', 'preco_venda'], ['estoque', 'estoque_minimo']);

        $dados = $request->validate([
            'nome' => 'required|string|min:2|max:120',
            'descricao' => 'nullable|string|max:1000',
            'codigo' => ['nullable', 'string', 'max:40', Rule::unique('produtos', 'codigo')->ignore($produto?->id)],
            'codigo_barras' => 'nullable|string|max:60',
            'preco_custo' => 'required|numeric|min:0|max:999999',
            'preco_venda' => 'required|numeric|min:0|max:999999',
            'estoque' => 'nullable|integer|min:0|max:999999',
            'estoque_minimo' => 'nullable|integer|min:0|max:999999',
            'unidade' => 'required|string|max:20',
            'categoria_id' => ['nullable', Rule::exists('produto_categorias', 'id')],
        ], [], ['categoria_id' => 'categoria', 'preco_custo' => 'preço de custo', 'preco_venda' => 'preço de venda']);

        return [
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'codigo' => $dados['codigo'] ?? null,
            'codigo_barras' => $dados['codigo_barras'] ?? null,
            'preco_custo' => Number::money($dados['preco_custo']),
            'preco_venda' => Number::money($dados['preco_venda']),
            'estoque' => (int) ($dados['estoque'] ?? 0),
            'estoque_minimo' => (int) ($dados['estoque_minimo'] ?? 0),
            'unidade' => $dados['unidade'],
            'categoria_id' => $dados['categoria_id'] ?? null,
            'ativo' => $request->boolean('ativo', true),
        ];
    }
}
