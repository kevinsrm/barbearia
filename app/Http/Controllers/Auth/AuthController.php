<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pessoa;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        $usuario = Usuario::where('email', $request->email)->first();
        
        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return back()->withErrors(['email' => 'Credenciais inválidas'])->withInput();
        }
        
        Auth::login($usuario);
        return redirect()->intended('/dashboard');
    }
    
    public function showRegisterForm()
    {
        return view('auth.register');
    }
    
    public function register(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|min:8|confirmed',
            'cpf' => 'required|string|unique:pessoas,cpf',
        ]);
        
        $pessoa = Pessoa::create([
            'nome' => $request->nome,
            'cpf' => $request->cpf,
            'email' => $request->email,
            'tipo_pessoa' => 'usuario',
        ]);
        
        $usuario = Usuario::create([
            'pessoa_id' => $pessoa->id,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        
        $usuario->assignRole('cliente');
        
        return redirect('/login')->with('success', 'Cadastro realizado com sucesso!');
    }
    
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'Deslogado com sucesso!');
    }
}
