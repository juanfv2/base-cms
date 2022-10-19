<?php

namespace Tests\APIs\Auth;

use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Misc\XUserVerified;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ZRegisterDriverApiTest extends TestCase
{
    use ApiTestTrait,
        WithoutMiddleware,
        DatabaseTransactions
        // RefreshDatabase
        // ...
    ;

    /** @test */
    public function api_create_a_driver()
    {
        $this->withoutExceptionHandling();

        $role    = Role::factory()->create(['id' => 3]);
        $e = 'juanfv2@gmail.com';
        $p = '123456';

        $credentials = [
            'email' => $e,
            'name'  => 'juanfv2',
            // 'name'                  => 'juanfv2',
            // 'firstName'             => 'juanfv2',
            // 'lastName'              => 'juanfv2',
            'password'              => $p,
            'password_confirmation' => $p,
            'cellPhone'             => $p,
            'imei'                  => $p,
            'role_id'               => $role->id,
        ];

        // Artisan::call('passport:install', ['-vvv' => true]);
        // Artisan::call('db:seed', ['-vvv' => true]);

        $response1 = $this->json('POST', route('api.register.register'), $credentials);

        $response = json_decode($response1->getContent());
        // dd($response);
        // dump($response);
        // $response->dump();

        $resp = "Le enviaremos un correo a $e, cuando nuestro equipo haya verificado los documentos";
        $this->assertEquals($resp, $response->message);
        $response1->assertOk();
    }
}
