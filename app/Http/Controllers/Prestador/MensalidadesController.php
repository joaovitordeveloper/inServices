<?php

namespace App\Http\Controllers\Prestador;

use App\Http\Controllers\Controller;
use App\Models\Mensalidade;
use Illuminate\View\View;

class MensalidadesController extends Controller
{
    public function index(): View
    {
        $prestador = auth()->user()->perfilPrestador()->with('assinatura.plano')->firstOrFail();

        return view('prestador.mensalidades.index', [
            'prestador' => $prestador,
            'mensalidades' => Mensalidade::with('assinatura.plano')
                ->where('prestador_id', $prestador->id)
                ->orderByDesc('data_vencimento')
                ->get(),
            'mensalidadeAberta' => Mensalidade::where('prestador_id', $prestador->id)
                ->whereIn('status', ['pendente', 'vencida'])
                ->orderBy('data_vencimento')
                ->first(),
        ]);
    }
}
