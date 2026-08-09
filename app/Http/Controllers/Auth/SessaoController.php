<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\EntrarRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SessaoController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(EntrarRequest $request): RedirectResponse
    {
        $credenciais = [
            'email' => $request->string('email')->lower()->toString(),
            'password' => $request->string('senha')->toString(),
        ];

        if (! Auth::attempt($credenciais, $request->boolean('lembrar'))) {
            throw ValidationException::withMessages([
                'email' => 'As credenciais informadas nao conferem.',
            ]);
        }

        $request->session()->regenerate();

        $rotaPadrao = Auth::user()?->tipo === 'administrador_geral'
            ? route('admin.painel')
            : route('prestador.painel');

        return redirect()->intended($rotaPadrao);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
