<?php

namespace App\Http\Controllers;

use App\Http\Requests\AlterarSenhaRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ContaController extends Controller
{
    public function edit(): View
    {
        return view('conta.edit');
    }

    public function update(AlterarSenhaRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->string('senha')->toString()),
        ]);

        return redirect()->route('conta.edit')->with('status', 'Senha alterada com sucesso.');
    }
}
