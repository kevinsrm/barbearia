<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Models\Pessoa;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $usuarios = Usuario::query()
            ->with('pessoa')
            ->when($request->filled('busca'), function ($q) use ($request) {
                $busca = trim((string) $request->string('busca'));
                $q->where(fn ($sub) => $sub->where('nome', 'like', "%{$busca}%")->orWhere('email', 'like', "%{$busca}%"));
            })
            ->orderBy('nome')
            ->paginate(config('barbearia.paginacao', 15))
            ->withQueryString();

        return view('usuarios.index', ['usuarios' => $usuarios, 'papeis' => Usuario::ROLES]);
    }

    public function create()
    {
        return view('usuarios.create', [
            'usuario' => new Usuario(['role' => Usuario::ROLE_GERENTE, 'ativo' => true]),
            'papeis' => Usuario::ROLES,
            'pessoas' => Pessoa::orderBy('nome')->get(['id', 'nome', 'tipo_pessoa']),
        ]);
    }

    public function store(Request $request)
    {
        $dados = $this->validar($request);

        $usuario = Usuario::create([
            'pessoa_id' => ($dados['pessoa_id'] ?? null) ?: null,
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'password' => $dados['password'] ?? null,
            'role' => $dados['role'],
            'ativo' => $request->boolean('ativo'),
        ]);

        $this->vincularFuncionario($usuario);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuário criado com sucesso!');
    }

    public function edit(Usuario $usuario)
    {
        return view('usuarios.edit', [
            'usuario' => $usuario,
            'papeis' => Usuario::ROLES,
            'pessoas' => Pessoa::orderBy('nome')->get(['id', 'nome', 'tipo_pessoa']),
        ]);
    }

    public function update(Request $request, Usuario $usuario)
    {
        $dados = $this->validar($request, $usuario);

        $usuario->fill([
            'pessoa_id' => ($dados['pessoa_id'] ?? null) ?: null,
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'role' => $dados['role'],
            'ativo' => $request->boolean('ativo'),
        ]);

        if (! empty($dados['password'])) {
            $usuario->password = $dados['password'];
        }

        $usuario->save();
        $this->vincularFuncionario($usuario);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuário atualizado!');
    }

    public function destroy(Request $request, Usuario $usuario)
    {
        if ($usuario->id === $request->user()?->id) {
            return back()->with('error', 'Você não pode excluir o seu próprio acesso.');
        }

        if ($usuario->role === Usuario::ROLE_ADMIN && Usuario::where('role', Usuario::ROLE_ADMIN)->where('ativo', true)->count() <= 1) {
            return back()->with('error', 'Mantenha ao menos um administrador ativo no sistema.');
        }

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuário removido.');
    }

    protected function validar(Request $request, ?Usuario $usuario = null): array
    {
        return $request->validate([
            'nome' => 'required|string|min:3|max:120',
            'email' => ['required', 'email', 'max:180', Rule::unique('usuarios', 'email')->ignore($usuario?->id)],
            'password' => [$usuario ? 'nullable' : 'required', 'string', 'confirmed', Password::min(6)],
            'role' => ['required', Rule::in(array_keys(Usuario::ROLES))],
            'pessoa_id' => 'nullable|exists:pessoas,id',
            'ativo' => 'nullable|boolean',
        ], [], ['password' => 'senha', 'role' => 'perfil', 'pessoa_id' => 'pessoa']);
    }

    protected function vincularFuncionario(Usuario $usuario): void
    {
        if (! $usuario->pessoa_id) {
            return;
        }

        $funcionario = Funcionario::where('pessoa_id', $usuario->pessoa_id)->first();

        if ($funcionario && $funcionario->usuario_id !== $usuario->id) {
            $funcionario->update(['usuario_id' => $usuario->id]);
        }
    }
}
