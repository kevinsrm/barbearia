<?php

namespace App\Http\Controllers;

use App\Models\Servico;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index()
    {
        $servicos = Servico::where('ativo', true)->with('categoria')->get();
        return view('site.index', compact('servicos'));
    }
    
    public function servicos()
    {
        $servicos = Servico::where('ativo', true)->with('categoria')->get();
        return view('site.servicos', compact('servicos'));
    }
    
    public function sobre()
    {
        return view('site.sobre');
    }
    
    public function agendamento()
    {
        $servicos = Servico::where('ativo', true)->get();
        return view('site.agendamento', compact('servicos'));
    }
}