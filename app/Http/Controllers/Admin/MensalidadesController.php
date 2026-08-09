<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mensalidade;
use Illuminate\View\View;

class MensalidadesController extends Controller
{
    public function index(): View
    {
        return view('admin.mensalidades.index', [
            'mensalidades' => Mensalidade::with(['prestador', 'assinatura.plano'])->latest('data_vencimento')->get(),
        ]);
    }
}
