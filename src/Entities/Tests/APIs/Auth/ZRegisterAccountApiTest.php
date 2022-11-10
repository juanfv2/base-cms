<?php

namespace Tests\APIs\Auth;

use Tests\TestCase;
use Juanfv2\BaseCms\Traits\ApiTestTrait;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Misc\XUserVerified;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ZRegisterAccountApiTest extends TestCase
{
    use ApiTestTrait,
        WithoutMiddleware,
        DatabaseTransactions
        // RefreshDatabase
        // ...
    ;

    /** @test */
    public function api_create_an_account()
    {
        $this->withoutExceptionHandling();

        $role    = Role::factory()->create(['id' => 4]);
        $e = 'juanfv2@gmail.com';
        $p = '123456';

        $credentials = [
            'email'                 => $e,
            'name'                  => 'juanfv2',
            'password'              => $p,
            'password_confirmation' => $p,
            'cellPhone'             => $p,
            'imei'                  => $p,
            'role_id'               => $role->id,
        ];

        // Artisan::call('passport:install', ['-vvv' => true]);
        // Artisan::call('db:seed', ['-vvv' => true]);

        $this->response = $this->json('POST', route('api.register.register'), $credentials);

        $this->response->assertOk();

        $response = $this->response->json();

        // dd($response);
        // dump($response);
        // $response->dump();
        $resp = "Se envió un correo a $e, verifique su correo";
        $this->assertEquals($resp, $response['message']);
    }

    /** @test */
    public function api_verify_an_account()
    {
        $this->withoutExceptionHandling();

        $role    = Role::factory()->create(['id' => 4]);
        $p = '123456';
        $e = 'juanfv2@gmail.com';

        $credentials = [
            'email'                 => $e,
            'name'                  => 'juanfv2',
            'password'              => $p,
            'password_confirmation' => $p,
            'imei'                  => $p,
            'role_id'               => $role->id,
        ];

        $response = $this->json('POST', route('api.register.register'), $credentials);

        $response->assertOk();

        $user = User::where('email', $e)->first();
        $userV = XUserVerified::where('user_id', $user->id)->first();

        $this->response = $this->json('POST', route('api.register.verifyUser', ['token' =>  $userV->token]));

        $this->response->assertOk();

        $response = $this->response->json();

        // dump($response);
        // $response->dump();
        $resp = "Su correo electrónico está verificado. Ahora puede iniciar sesión.";
        $this->assertEquals($resp, $response['data']['description']);
    }
}
