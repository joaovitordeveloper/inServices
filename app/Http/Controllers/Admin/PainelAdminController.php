<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mensalidade;
use App\Models\PerfilPrestador;
use App\Models\Plano;
use Illuminate\View\View;

class PainelAdminController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.painel', [
            'prestadoresAtivos' => PerfilPrestador::where('status', 'ativo')->count(),
            'prestadoresTeste' => PerfilPrestador::where('status', 'teste')->count(),
            'prestadoresSuspensos' => PerfilPrestador::where('status', 'suspenso')->count(),
            'mensalidadesVencidas' => Mensalidade::where('status', 'vencida')->count(),
            'receitaMensal' => Mensalidade::where('status', 'paga')->whereMonth('data_pagamento', now()->month)->sum('valor_final'),
            'planos' => Plano::withCount('assinaturas')->orderBy('nome')->get(),
            'prestadores' => PerfilPrestador::with(['usuario', 'assinatura.plano'])->latest()->get(),
        ]);
    }
}
