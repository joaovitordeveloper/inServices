<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_master_consegue_logar(): void
    {
        User::factory()->create([
            'email' => 'developer.joaovitor@gmail.com',
            'password' => Hash::make('123456789'),
            'tipo' => 'administrador_geral',
        ]);

        $this->get('/login')
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store, private');

        $this->post('/login', [
            'email' => 'developer.joaovitor@gmail.com',
            'senha' => '123456789',
        ])->assertRedirect('/admin');

        $this->assertAuthenticated();
    }
}
