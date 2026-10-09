<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Pessoa;
use App\Models\Usuario;
use App\Support\Number;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

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

        $usuario = Usuario::where('email', $credenciais['email'])->first();

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
        $dados = $request->validate([
            'nome' => 'required|string|min:3|max:120',
            'email' => 'required|email|max:180|unique:usuarios,email',
            'telefone' => 'required|string|min:8|max:30',
            'cpf' => 'nullable|string|max:20|unique:pessoas,cpf',
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [], ['cpf' => 'CPF', 'password' => 'senha']);

        $pessoa = Pessoa::create([
            'nome' => $dados['nome'],
            'tipo_pessoa' => 'cliente',
            'cpf' => Number::somenteDigitos($dados['cpf'] ?? null),
            'telefone' => $dados['telefone'],
            'email' => $dados['email'],
            'ativo' => true,
        ]);

        Cliente::create(['pessoa_id' => $pessoa->id, 'status' => 'ativo']);

        $usuario = Usuario::create([
            'pessoa_id' => $pessoa->id,
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'password' => $dados['password'],
            'role' => Usuario::ROLE_CLIENTE,
            'ativo' => true,
        ]);

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
