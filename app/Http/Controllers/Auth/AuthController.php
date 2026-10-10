<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Pessoa;
use App\Models\Usuario;
use App\Support\Number;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credenciais = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [], ['email' => 'e-mail', 'password' => 'senha']);

        $usuario = Usuario::where('email', strtolower(trim($credenciais['email'])))->first();

        if (! $usuario || ! Hash::check($credenciais['password'], $usuario->password)) {
            return back()->withErrors(['email' => 'E-mail ou senha inválidos.'])->withInput($request->only('email'));
        }

        if (! $usuario->ativo) {
            return back()->withErrors(['email' => 'Este acesso está desativado. Fale com o administrador.'])->withInput($request->only('email'));
        }

        Auth::login($usuario, $request->boolean('remember'));
        $request->session()->regenerate();

        $usuario->forceFill(['ultimo_acesso' => now()])->save();

        return redirect()->intended($usuario->destinoPosLogin());
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Cadastro público: cria a pessoa, o cliente e o usuário com papel "cliente".
     */
    public function register(Request $request)
    {
        // Padroniza os valores antes da validação: CPF com máscara não pode
        // contornar a regra de unicidade e e-mails ficam consistentes em MySQL/SQLite.
        $email = $request->input('email');
        $cpf = $request->input('cpf');
        $request->merge([
            'email' => is_string($email) ? strtolower(trim($email)) : $email,
            'cpf' => is_string($cpf) ? Number::somenteDigitos($cpf) : $cpf,
        ]);

        // Um cadastro público anterior pode ter criado Pessoa/Cliente antes de
        // falhar ao salvar Usuario. Reutilizamos esse registro em vez de bloquear
        // o reenvio pelo índice único de pessoas.email.
        $emailNormalizado = $request->input('email');
        $pessoaExistente = is_string($emailNormalizado) && $emailNormalizado !== ''
            ? Pessoa::where('email', $emailNormalizado)->first()
            : null;

        $cpfUnico = Rule::unique('pessoas', 'cpf');

        if ($pessoaExistente) {
            $cpfUnico->ignore($pessoaExistente->id);
        }

        $dados = $request->validate([
            'nome' => 'required|string|min:3|max:120',
            'email' => 'required|email|max:180|unique:usuarios,email',
            'telefone' => 'required|string|min:8|max:30',
            'cpf' => ['nullable', 'string', 'max:20', $cpfUnico],
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [], ['cpf' => 'CPF', 'password' => 'senha']);

        // Uma falha ao criar qualquer vínculo não deixa pessoa/cliente órfão no banco.
        $usuario = DB::transaction(function () use ($dados) {
            $pessoa = Pessoa::where('email', $dados['email'])->lockForUpdate()->first();

            if ($pessoa) {
                if ($pessoa->tipo_pessoa !== 'cliente' || Usuario::where('pessoa_id', $pessoa->id)->exists()) {
                    throw ValidationException::withMessages([
                        'email' => 'Este e-mail já está associado a outro cadastro.',
                    ]);
                }

                $pessoa->update([
                    'nome' => $dados['nome'],
                    'cpf' => $dados['cpf'] ?? $pessoa->cpf,
                    'telefone' => $dados['telefone'],
                    'ativo' => true,
                ]);
            } else {
                $pessoa = Pessoa::create([
                    'nome' => $dados['nome'],
                    'tipo_pessoa' => 'cliente',
                    'cpf' => $dados['cpf'] ?? null,
                    'telefone' => $dados['telefone'],
                    'email' => $dados['email'],
                    'ativo' => true,
                ]);
            }

            Cliente::updateOrCreate(
                ['pessoa_id' => $pessoa->id],
                ['status' => 'ativo']
            );

            return Usuario::create([
                'pessoa_id' => $pessoa->id,
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'password' => $dados['password'],
                'role' => Usuario::ROLE_CLIENTE,
                'ativo' => true,
            ]);
        });

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->route('site.agendamento')->with('success', 'Cadastro realizado! Agora é só agendar seu horário.');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('site.index')->with('success', 'Você saiu do sistema.');
    }
}
