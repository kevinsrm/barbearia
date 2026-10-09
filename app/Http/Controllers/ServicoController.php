<?php

namespace App\Http\Controllers;

use App\Models\Servico;
use App\Models\ServicoCategoria;
use Illuminate\Http\Request;

class ServicoController extends Controller
{
    public function index()
    {
        $servicos = Servico::with('categoria')
            ->orderBy('categoria_id')
            ->paginate(10);
        $categorias = ServicoCategoria::orderBy('ordem')->get();
        return view('servicos.index', compact('servicos', 'categorias'));
    }
    
    public function create()
    {
        $categorias = ServicoCategoria::orderBy('ordem')->get();
        return view('servicos.create', compact('categorias'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'duracao' => 'required|integer|min:1',
            'valor' => 'required|numeric|min:0',
            'servico_categoria_id' => 'required|exists:servico_categorias,id',
        ]);
        
        Servico::create($request->all());
        return redirect()->route('servicos.index')->with('success', 'Serviço cadastrado com sucesso!');
    }
    
    public function edit(Servico $servico)
    {
        $categorias = ServicoCategoria::orderBy('ordem')->get();
        return view('servicos.edit', compact('servico', 'categorias'));
    }
    
    public function update(Request $request, Servico $servico)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'duracao' => 'required|integer|min:1',
            'valor' => 'required|numeric|min:0',
            'servico_categoria_id' => 'required|exists:servico_categorias,id',
        ]);
        
        $servico->update($request->all());
        return redirect()->route('servicos.index')->with('success', 'Serviço atualizado com sucesso!');
    }
    
    public function destroy(Servico $servico)
    {
        $servico->delete();
        return redirect()->route('servicos.index')->with('success', 'Serviço excluído com sucesso!');
    }
}
