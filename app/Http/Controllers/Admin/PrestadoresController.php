<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PerfilPrestador;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PrestadoresController extends Controller
{
    public function index(): View
    {
        return view('admin.prestadores.index', [
            'prestadores' => PerfilPrestador::with(['usuario', 'assinatura.plano'])->latest()->get(),
        ]);
    }

    public function alternarStatus(PerfilPrestador $prestador): RedirectResponse
    {
        $prestador->update([
            'status' => $prestador->status === 'ativo' ? 'suspenso' : 'ativo',
        ]);

        return redirect()
            ->route('admin.prestadores.index')
            ->with('status', $prestador->status === 'ativo' ? 'Prestador ativado.' : 'Prestador desativado.');
    }
}
