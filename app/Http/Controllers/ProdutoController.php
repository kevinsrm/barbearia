<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\ProdutoCategoria;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function index()
    {
        $produtos = Produto::with('categoria')
            ->orderBy('nome')
            ->paginate(10);
        $categorias = ProdutoCategoria::orderBy('ordem')->get();
        return view('produtos.index', compact('produtos', 'categorias'));
    }
    
    public function create()
    {
        $categorias = ProdutoCategoria::orderBy('ordem')->get();
        return view('produtos.create', compact('categorias'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'codigo' => 'required|string|unique:produtos,codigo',
            'preco_custo' => 'required|numeric|min:0',
            'preco_venda' => 'required|numeric|min:0',
            'estoque_minimo' => 'required|integer|min:0',
            'unidade' => 'required|string',
            'categoria_id' => 'required|exists:produto_categorias,id',
        ]);
        
        Produto::create($request->all());
        return redirect()->route('produtos.index')->with('success', 'Produto cadastrado com sucesso!');
    }
    
    public function edit(Produto $produto)
    {
        $categorias = ProdutoCategoria::orderBy('ordem')->get();
        return view('produtos.edit', compact('produto', 'categorias'));
    }
    
    public function update(Request $request, Produto $produto)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'codigo' => 'required|string|unique:produtos,codigo,' . $produto->id,
            'preco_custo' => 'required|numeric|min:0',
            'preco_venda' => 'required|numeric|min:0',
            'estoque_minimo' => 'required|integer|min:0',
        ]);
        
        $produto->update($request->all());
        return redirect()->route('produtos.index')->with('success', 'Produto atualizado com sucesso!');
    }
    
    public function destroy(Produto $produto)
    {
        $produto->delete();
        return redirect()->route('produtos.index')->with('success', 'Produto excluído com sucesso!');
    }
}
