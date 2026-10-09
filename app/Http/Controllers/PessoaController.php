<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class PessoaController extends Controller
{
    public function index()
    {
        $pessoas = Pessoa::with('usuario')
            ->orderBy('nome')
            ->paginate(10);
        return view('pessoas.index', compact('pessoas'));
    }
    
    public function create()
    {
        return view('pessoas.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|unique:pessoas,cpf',
            'telefone' => 'required|string',
            'email' => 'required|email|unique:pessoas,email',
            'tipo_pessoa' => 'required|in:usuario,funcionario,cliente,fornecedor',
        ]);
        
        $pessoa = Pessoa::create($request->all());
        
        if ($request->tipo_pessoa == 'usuario') {
            $usuario = Usuario::create([
                'pessoa_id' => $pessoa->id,
                'email' => $request->email,
                'password' => Hash::make('12345678'),
            ]);
            $usuario->assignRole('cliente');
        }
        
        return redirect()->route('pessoas.index')->with('success', 'Pessoa cadastrada com sucesso!');
    }
    
    public function edit(Pessoa $pessoa)
    {
        return view('pessoas.edit', compact('pessoa'));
    }
    
    public function update(Request $request, Pessoa $pessoa)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|unique:pessoas,cpf,' . $pessoa->id,
            'telefone' => 'required|string',
            'email' => 'required|email|unique:pessoas,email,' . $pessoa->id,
        ]);
        
        $pessoa->update($request->all());
        return redirect()->route('pessoas.index')->with('success', 'Pessoa atualizada com sucesso!');
    }
    
    public function destroy(Pessoa $pessoa)
    {
        if ($pessoa->usuario) {
            $pessoa->usuario->delete();
        }
        $pessoa->delete();
        return redirect()->route('pessoas.index')->with('success', 'Pessoa excluída com sucesso!');
    }
}
