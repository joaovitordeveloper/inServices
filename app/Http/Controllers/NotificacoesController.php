<?php

namespace App\Http\Controllers;

use App\Models\Notificacao;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class NotificacoesController extends Controller
{
    public function index(): JsonResponse
    {
        $notificacoes = Notificacao::query()
            ->where('destinatario_type', User::class)
            ->where('destinatario_id', auth()->id())
            ->latest()
            ->limit(6)
            ->get();

        return response()->json([
            'nao_lidas' => Notificacao::query()
                ->where('destinatario_type', User::class)
                ->where('destinatario_id', auth()->id())
                ->whereNull('lida_em')
                ->count(),
            'ultima_id' => $notificacoes->max('id') ?? 0,
            'notificacoes' => $notificacoes->map(fn (Notificacao $notificacao): array => [
                'id' => $notificacao->id,
                'titulo' => $notificacao->titulo,
                'corpo' => $notificacao->corpo,
                'url' => $notificacao->url,
                'lida' => (bool) $notificacao->lida_em,
                'criada_em' => $notificacao->created_at->format('d/m H:i'),
            ])->values(),
        ]);
    }

    public function marcarLidas(): RedirectResponse
    {
        Notificacao::query()
            ->where('destinatario_type', User::class)
            ->where('destinatario_id', auth()->id())
            ->whereNull('lida_em')
            ->update(['lida_em' => now()]);

        return back()->with('status', 'Notificacoes marcadas como lidas.');
    }
}
