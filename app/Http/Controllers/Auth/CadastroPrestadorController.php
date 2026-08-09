<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CadastrarPrestadorRequest;
use App\Models\Assinatura;
use App\Models\Mensalidade;
use App\Models\PerfilPrestador;
use App\Models\Plano;
use App\Models\User;
use App\Services\ServicoTelefone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CadastroPrestadorController extends Controller
{
    public function create(): View
    {
        return view('auth.cadastro-prestador', [
            'planos' => Plano::where('ativo', true)->orderBy('valor_mensal')->get(),
        ]);
    }

    public function store(CadastrarPrestadorRequest $request, ServicoTelefone $telefones): RedirectResponse
    {
        $usuario = DB::transaction(function () use ($request, $telefones): User {
            $plano = Plano::lockForUpdate()->findOrFail($request->integer('plano_id'));

            $usuario = User::create([
                'name' => $request->string('nome_responsavel')->toString(),
                'email' => $request->string('email')->lower()->toString(),
                'tipo' => 'prestador',
                'telefone' => $request->string('telefone')->toString(),
                'telefone_normalizado' => $telefones->normalizarBrasil($request->string('telefone')->toString()),
                'password' => Hash::make($request->string('senha')->toString()),
            ]);

            $slugBase = Str::slug($request->string('nome_publico')->toString());
            $slug = $slugBase;
            $contador = 2;

            while (PerfilPrestador::where('slug', $slug)->exists()) {
                $slug = $slugBase.'-'.$contador;
                $contador++;
            }

            $prestador = PerfilPrestador::create([
                'usuario_id' => $usuario->id,
                'nome_publico' => $request->string('nome_publico')->toString(),
                'slug' => $slug,
                'telefone' => $request->string('telefone')->toString(),
                'telefone_normalizado' => $telefones->normalizarBrasil($request->string('telefone')->toString()),
                'email_publico' => $request->string('email')->lower()->toString(),
                'status' => 'teste',
            ]);

            $assinatura = Assinatura::create([
                'prestador_id' => $prestador->id,
                'plano_id' => $plano->id,
                'status' => 'teste',
                'data_inicio' => now()->toDateString(),
                'data_proximo_vencimento' => now()->addMonth()->toDateString(),
                'periodo_gratuito_ate' => now()->addDays(7)->toDateString(),
                'renovacao_automatica' => false,
                'observacoes' => 'Assinatura criada pelo cadastro publico.',
            ]);

            Mensalidade::create([
                'prestador_id' => $prestador->id,
                'assinatura_id' => $assinatura->id,
                'plano_id' => $plano->id,
                'competencia' => now()->startOfMonth()->toDateString(),
                'valor_original' => $plano->valor_mensal,
                'valor_final' => $plano->valor_mensal,
                'data_emissao' => now()->toDateString(),
                'data_vencimento' => now()->addDays(7)->toDateString(),
                'status' => 'pendente',
                'observacao' => 'Mensalidade inicial gerada no cadastro.',
            ]);

            return $usuario;
        });

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->route('prestador.painel');
    }
}
