<?php

use App\Http\Controllers\Admin\MensalidadesController;
use App\Http\Controllers\Admin\PainelAdminController;
use App\Http\Controllers\Admin\PlanosController;
use App\Http\Controllers\Admin\PrestadoresController;
use App\Http\Controllers\Auth\CadastroPrestadorController;
use App\Http\Controllers\Auth\SessaoController;
use App\Http\Controllers\ContaController;
use App\Http\Controllers\NotificacoesController;
use App\Http\Controllers\Prestador\AgendaController;
use App\Http\Controllers\Prestador\AssinaturaController;
use App\Http\Controllers\Prestador\ClientesController;
use App\Http\Controllers\Prestador\MensagensWhatsappController;
use App\Http\Controllers\Prestador\MensalidadesController as PrestadorMensalidadesController;
use App\Http\Controllers\Prestador\PainelPrestadorController;
use App\Http\Controllers\Prestador\ProfissionaisController;
use App\Http\Controllers\Prestador\ServicosController;
use App\Http\Controllers\Publico\AgendamentoPublicoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    if (auth()->user()?->tipo === 'administrador_geral') {
        return redirect()->route('admin.painel');
    }

    return redirect()->route('prestador.painel');
})->name('inicio');

Route::middleware('guest')->group(function () {
    Route::get('/login', [SessaoController::class, 'create'])->name('login');
    Route::post('/login', [SessaoController::class, 'store'])->name('login.store');
    Route::get('/cadastro', [CadastroPrestadorController::class, 'create'])->name('cadastro.prestador');
    Route::post('/cadastro', [CadastroPrestadorController::class, 'store'])->name('cadastro.prestador.store');
});

Route::post('/logout', [SessaoController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/minha-conta', [ContaController::class, 'edit'])->name('conta.edit');
    Route::put('/minha-conta/senha', [ContaController::class, 'update'])->name('conta.senha.update');
    Route::get('/notificacoes', [NotificacoesController::class, 'index'])->name('notificacoes.index');
    Route::patch('/notificacoes/lidas', [NotificacoesController::class, 'marcarLidas'])->name('notificacoes.lidas');

    Route::get('/admin', PainelAdminController::class)->name('admin.painel');
    Route::get('/admin/prestadores', [PrestadoresController::class, 'index'])->name('admin.prestadores.index');
    Route::patch('/admin/prestadores/{prestador}/status', [PrestadoresController::class, 'alternarStatus'])->name('admin.prestadores.status');
    Route::get('/admin/planos', [PlanosController::class, 'index'])->name('admin.planos.index');
    Route::get('/admin/planos/criar', [PlanosController::class, 'create'])->name('admin.planos.create');
    Route::post('/admin/planos', [PlanosController::class, 'store'])->name('admin.planos.store');
    Route::get('/admin/planos/{plano}/editar', [PlanosController::class, 'edit'])->name('admin.planos.edit');
    Route::put('/admin/planos/{plano}', [PlanosController::class, 'update'])->name('admin.planos.update');
    Route::delete('/admin/planos/{plano}', [PlanosController::class, 'destroy'])->name('admin.planos.destroy');
    Route::get('/admin/mensalidades', [MensalidadesController::class, 'index'])->name('admin.mensalidades.index');
    Route::get('/painel', PainelPrestadorController::class)->name('prestador.painel');
    Route::get('/painel/dados', [PainelPrestadorController::class, 'dados'])->name('prestador.painel.dados');
    Route::get('/painel/servicos', [ServicosController::class, 'index'])->name('prestador.servicos.index');
    Route::get('/painel/servicos/criar', [ServicosController::class, 'create'])->name('prestador.servicos.create');
    Route::post('/painel/servicos', [ServicosController::class, 'store'])->name('prestador.servicos.store');
    Route::get('/painel/servicos/{servico}/editar', [ServicosController::class, 'edit'])->name('prestador.servicos.edit');
    Route::put('/painel/servicos/{servico}', [ServicosController::class, 'update'])->name('prestador.servicos.update');
    Route::delete('/painel/servicos/{servico}', [ServicosController::class, 'destroy'])->name('prestador.servicos.destroy');
    Route::get('/painel/profissionais', [ProfissionaisController::class, 'index'])->name('prestador.profissionais.index');
    Route::post('/painel/profissionais', [ProfissionaisController::class, 'store'])->name('prestador.profissionais.store');
    Route::get('/painel/profissionais/{profissional}/editar', [ProfissionaisController::class, 'edit'])->name('prestador.profissionais.edit');
    Route::put('/painel/profissionais/{profissional}', [ProfissionaisController::class, 'update'])->name('prestador.profissionais.update');
    Route::patch('/painel/profissionais/{profissional}/status', [ProfissionaisController::class, 'alternarStatus'])->name('prestador.profissionais.status');
    Route::delete('/painel/profissionais/{profissional}', [ProfissionaisController::class, 'destroy'])->name('prestador.profissionais.destroy');
    Route::get('/painel/agenda', [AgendaController::class, 'index'])->name('prestador.agenda.index');
    Route::post('/painel/agenda/horarios', [AgendaController::class, 'storeRegra'])->name('prestador.agenda.regras.store');
    Route::delete('/painel/agenda/horarios/{regra}', [AgendaController::class, 'destroyRegra'])->name('prestador.agenda.regras.destroy');
    Route::get('/painel/clientes', [ClientesController::class, 'index'])->name('prestador.clientes.index');
    Route::get('/painel/clientes/{cliente}/editar', [ClientesController::class, 'edit'])->name('prestador.clientes.edit');
    Route::put('/painel/clientes/{cliente}', [ClientesController::class, 'update'])->name('prestador.clientes.update');
    Route::get('/painel/assinatura', [AssinaturaController::class, 'edit'])->name('prestador.assinatura.edit');
    Route::put('/painel/assinatura', [AssinaturaController::class, 'update'])->name('prestador.assinatura.update');
    Route::get('/painel/mensalidade', [PrestadorMensalidadesController::class, 'index'])->name('prestador.mensalidades.index');
    Route::get('/painel/mensagens-whatsapp', [MensagensWhatsappController::class, 'edit'])->name('prestador.mensagens-whatsapp.edit');
    Route::put('/painel/mensagens-whatsapp', [MensagensWhatsappController::class, 'update'])->name('prestador.mensagens-whatsapp.update');
});

Route::prefix('agendar/{prestador:uuid_publico}')->name('publico.agendamento.')->group(function () {
    Route::get('/', [AgendamentoPublicoController::class, 'index'])->name('index');
    Route::post('/identificar', [AgendamentoPublicoController::class, 'identificar'])->name('identificar');
    Route::get('/{servico:uuid_publico}', [AgendamentoPublicoController::class, 'servico'])->name('servico');
    Route::get('/{servico:uuid_publico}/horarios', [AgendamentoPublicoController::class, 'horarios'])->name('horarios');
    Route::post('/{servico:uuid_publico}/confirmar', [AgendamentoPublicoController::class, 'confirmar'])->name('confirmar');
});
