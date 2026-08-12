<?php

namespace App\Http\Controllers\Publico;

use App\Actions\VerificarAcessoPrestador;
use App\Enums\StatusAgendamento;
use App\Http\Controllers\Controller;
use App\Http\Requests\IdentificarClientePublicoRequest;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\HistoricoStatusAgendamento;
use App\Models\Notificacao;
use App\Models\PerfilPrestador;
use App\Models\Servico;
use App\Models\User;
use App\Services\CalcularHorariosDisponiveis;
use App\Services\ServicoTelefone;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AgendamentoPublicoController extends Controller
{
    public function index(PerfilPrestador $prestador, VerificarAcessoPrestador $acesso): View
    {
        $cliente = $this->clienteIdentificado($prestador);

        return view('publico.agendar', [
            'prestador' => $prestador,
            'servicoSelecionado' => null,
            'servicos' => $prestador->servicos()->where('status', 'publicado')->orderBy('ordem_exibicao')->get(),
            'acesso' => $acesso->executar($prestador),
            'clienteIdentificado' => $cliente,
            'meusAgendamentos' => $this->agendamentosDoCliente($prestador, $cliente),
        ]);
    }

    public function identificar(IdentificarClientePublicoRequest $request, PerfilPrestador $prestador, ServicoTelefone $telefones): RedirectResponse
    {
        $telefoneNormalizado = $telefones->normalizarBrasil($request->string('telefone')->toString());

        if (! $telefoneNormalizado) {
            throw ValidationException::withMessages([
                'telefone' => 'Informe um telefone valido para continuar.',
            ]);
        }

        $cliente = Cliente::updateOrCreate(
            [
                'prestador_id' => $prestador->id,
                'telefone_normalizado' => $telefoneNormalizado,
            ],
            [
                'nome' => $request->string('nome')->trim()->toString(),
                'telefone' => $request->string('telefone')->trim()->toString(),
                'consentimento_privacidade_em' => now(),
            ],
        );

        session([$this->chaveCliente($prestador) => $cliente->id]);

        return redirect()->route('publico.agendamento.index', ['prestador' => $prestador->uuid_publico]);
    }

    public function servico(PerfilPrestador $prestador, Servico $servico, VerificarAcessoPrestador $acesso): View|RedirectResponse
    {
        abort_unless($servico->prestador_id === $prestador->id, 404);

        $cliente = $this->clienteIdentificado($prestador);

        if (! $cliente) {
            return redirect()->route('publico.agendamento.index', ['prestador' => $prestador->uuid_publico]);
        }

        return view('publico.agendar', [
            'prestador' => $prestador,
            'servicoSelecionado' => $servico->load('profissionais'),
            'servicos' => $prestador->servicos()->where('status', 'publicado')->orderBy('ordem_exibicao')->get(),
            'acesso' => $acesso->executar($prestador),
            'clienteIdentificado' => $cliente,
            'meusAgendamentos' => $this->agendamentosDoCliente($prestador, $cliente),
        ]);
    }

    public function horarios(Request $request, PerfilPrestador $prestador, Servico $servico, CalcularHorariosDisponiveis $calcular, VerificarAcessoPrestador $acesso): JsonResponse
    {
        abort_unless($servico->prestador_id === $prestador->id, 404);
        abort_unless($this->clienteIdentificado($prestador), 403);

        $resultadoAcesso = $acesso->executar($prestador);

        if (! $resultadoAcesso['permitido']) {
            return response()->json([
                'mensagem' => $resultadoAcesso['alerta'] ?? 'Agenda temporariamente indisponivel.',
            ], 423);
        }

        $data = CarbonImmutable::parse($request->query('data', now()->toDateString()), config('app.timezone'));

        return response()->json([
            'horarios' => $calcular->executar($servico, $data)->values(),
        ]);
    }

    public function confirmar(Request $request, PerfilPrestador $prestador, Servico $servico, CalcularHorariosDisponiveis $calcular, VerificarAcessoPrestador $acesso): JsonResponse
    {
        abort_unless($servico->prestador_id === $prestador->id, 404);

        $cliente = $this->clienteIdentificado($prestador);
        abort_unless($cliente, 403);

        $resultadoAcesso = $acesso->executar($prestador);

        if (! $resultadoAcesso['permitido']) {
            return response()->json([
                'mensagem' => $resultadoAcesso['alerta'] ?? 'Agenda temporariamente indisponivel.',
            ], 423);
        }

        $dados = $request->validate([
            'profissional_id' => ['required', 'integer', 'exists:profissionais,id'],
            'inicio' => ['required', 'date'],
            'fim' => ['required', 'date', 'after:inicio'],
        ]);

        $inicio = CarbonImmutable::parse($dados['inicio'], config('app.timezone'));
        $fim = CarbonImmutable::parse($dados['fim'], config('app.timezone'));
        $horarioAindaDisponivel = $calcular->executar($servico, $inicio)
            ->contains(fn (array $horario) => (int) $horario['profissional_id'] === (int) $dados['profissional_id']
                && $horario['inicio'] === $inicio->toDateTimeString()
                && $horario['fim'] === $fim->toDateTimeString());

        if (! $horarioAindaDisponivel) {
            return response()->json([
                'mensagem' => 'Esse horario acabou de ficar indisponivel. Escolha outro horario.',
            ], 422);
        }

        $chaveIdempotencia = hash('sha256', $cliente->id.'|'.$servico->id.'|'.$dados['profissional_id'].'|'.$inicio->toDateTimeString());

        $agendamento = Agendamento::firstOrCreate(
            [
                'prestador_id' => $prestador->id,
                'chave_idempotencia' => $chaveIdempotencia,
            ],
            [
                'protocolo_publico' => 'AG-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                'cliente_id' => $cliente->id,
                'servico_id' => $servico->id,
                'profissional_id' => (int) $dados['profissional_id'],
                'inicio_em' => $inicio,
                'fim_em' => $fim,
                'status' => StatusAgendamento::Pendente->value,
            ],
        );

        if ($agendamento->wasRecentlyCreated) {
            HistoricoStatusAgendamento::create([
                'agendamento_id' => $agendamento->id,
                'novo_status' => $agendamento->status,
                'registrado_em' => now(),
                'observacao' => 'Agendamento criado pelo link publico.',
            ]);

            Notificacao::create([
                'destinatario_type' => User::class,
                'destinatario_id' => $prestador->usuario_id,
                'prestador_id' => $prestador->id,
                'titulo' => 'Novo agendamento',
                'corpo' => $cliente->nome.' agendou '.$servico->nome.' para '.$inicio->format('d/m/Y').' as '.$inicio->format('H:i'),
                'url' => route('prestador.painel'),
                'dados' => [
                    'agendamento_id' => $agendamento->id,
                    'protocolo' => $agendamento->protocolo_publico,
                ],
            ]);
        }

        $agendamento->load('profissional');

        return response()->json([
            'mensagem' => 'Agendamento salvo com sucesso.',
            'agendamento' => [
                'protocolo' => $agendamento->protocolo_publico,
                'servico' => $servico->nome,
                'profissional' => $agendamento->profissional->nome,
                'horario' => $agendamento->inicio_em->format('d/m/Y H:i'),
            ],
        ], 201);
    }

    private function clienteIdentificado(PerfilPrestador $prestador): ?Cliente
    {
        $clienteId = session($this->chaveCliente($prestador));

        if (! $clienteId) {
            return null;
        }

        return Cliente::query()
            ->where('prestador_id', $prestador->id)
            ->find($clienteId);
    }

    private function chaveCliente(PerfilPrestador $prestador): string
    {
        return 'cliente_publico_'.$prestador->id;
    }

    private function agendamentosDoCliente(PerfilPrestador $prestador, ?Cliente $cliente)
    {
        if (! $cliente) {
            return collect();
        }

        return $cliente->agendamentos()
            ->with(['servico', 'profissional'])
            ->where('prestador_id', $prestador->id)
            ->where('inicio_em', '>=', now()->subDay())
            ->whereNotIn('status', [StatusAgendamento::CanceladoPeloCliente->value, StatusAgendamento::CanceladoPeloPrestador->value])
            ->orderBy('inicio_em')
            ->limit(5)
            ->get();
    }
}
