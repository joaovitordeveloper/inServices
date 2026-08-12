<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\EntrarRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SessaoController extends Controller
{
    public function create(): Response
    {
        return response()
            ->view('auth.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
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
