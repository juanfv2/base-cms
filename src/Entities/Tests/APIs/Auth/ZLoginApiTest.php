<?php

namespace Tests\APIs\Auth;

use Tests\TestCase;

use Tests\ApiTestTrait;
use App\Models\Auth\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ZLoginApiTest extends TestCase
{
    use ApiTestTrait,
        WithoutMiddleware,
        DatabaseTransactions
        // RefreshDatabase
        // ...
    ;

    /** @test */
    public function login_user_person()
    {
        $this->withoutExceptionHandling();
        // $this->artisan('passport:install');

        $user        = User::factory()->create();
        $p           = '123456';
        $credentials = [
            'email' => $user->email,
            'password' => $p,
            'includes' => ['person'],
        ];

        $this->response = $this->json('POST', route('api.login.login'), $credentials);

        // dd($this->response->json());

        $this->assertJsonModifications();
    }

    /** @test */
    public function logout_user()
    {
        $this->withoutExceptionHandling();
        // $this->artisan('passport:install');

        $user        = User::factory()->create(['disabled' => 0,]);
        $p           = '123456';
        $credentials = [
            'email' => $user->email,
            'password' => $p,
            'includes' => ['person', 'token'],
        ];

        $this->response = $this->json('POST', route('api.login.login'), $credentials);

        $this->assertApiSuccess();
        $this->responseContent = $this->response->json();

        // dd($this->responseContent);

        $token = $this->responseContent['data']['token'];

        $this->response = $this->actingAsAdmin()->json('POST', route('api.login.logout'), [], ['Authorization' => 'Bearer ' . $token]);
        // $this->response->dump();

        $this->response->assertNoContent();
    }

    /** @test */
    public function login_bad_email()
    {
        $this->withoutExceptionHandling();

        $p           = '123456';
        $credentials = [
            'email' => '1admin@demo.com',
            'password' => $p,
            'includes' => ['person'],
        ];
        $this->response = $this->json('POST', route('api.login.login'), $credentials);

        $this->response->assertStatus(404);

        $this->responseContent = $this->response->json();
        $actual                = $this->responseContent['message'];
        $expected              = __('passwords.user');
        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function login_bad_password()
    {
        $this->withoutExceptionHandling();

        $user        = User::factory()->create();
        $p           = '123456.';
        $credentials = [
            'email' => $user->email,
            'password' => $p,
            'includes' => ['person'],
        ];
        $this->response = $this->json('POST', route('api.login.login'), $credentials);

        $this->response->assertStatus(422);

        $this->responseContent = $this->response->json();
        $actual                = $this->responseContent['message'];
        $expected              = __('auth.failed');

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function login_inactive_user()
    {
        $this->withoutExceptionHandling();

        $user        = User::factory()->create(['disabled' => 1,]);
        $p           = '123456';
        $credentials = [
            'email' => $user->email,
            'password' => $p,
            'includes' => ['person'],
        ];
        $this->response = $this->json('POST', route('api.login.login'), $credentials);

        $this->response->assertStatus(422);

        $this->responseContent = $this->response->json();
        $actual                = $this->responseContent['message'];
        $expected              = __('auth.no.active');

        $this->assertEquals($expected, $actual);
    }
}
