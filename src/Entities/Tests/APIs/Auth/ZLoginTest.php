<?php

namespace Tests\APIs\Auth;

use App\Models\Auth\Person;
use App\Models\Auth\User;
use Tests\TestCase;
use Tests\ApiTestTrait;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ZLoginTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    /** @test */
    public function login_user()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();

        $p = '123456';

        $credentials = [
            'email' => $user->email,
            'password' => $p,
            'includes' => ["person"]
        ];

        $this->response = $this->json('POST', '/api/authenticate', $credentials);
        // dd($response);
        // $this->response->dump();

        $this->assertApiResponse($user->toArray());
    }

    /** @test */
    public function logout_user()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();

        $p = '123456';

        $credentials = [
            'email' => $user->email,
            'password' => $p,
            'includes' => ['person', 'token']
        ];

        $this->response = $this->json('POST', '/api/authenticate', $credentials);
        // dd($this->response);
        // $this->response->dump();
        $this->assertApiSuccess();

        $this->response = json_decode($this->response->getContent(), true);
        $token = $this->response['data']['token'];

        $this->response = $this->json(
            'POST',
            '/api/logout',
            [],
            ['Authorization' =>  'Bearer ' . $token]
        );
        // $this->response->dump();

        $this->response->assertNoContent();
    }

    /** @test */
    public function login_bad_email()
    {
        $this->withoutExceptionHandling();

        $p = '123456';

        $credentials = [
            'email' => '1admin@demo.com',
            'password' => $p,
            'includes' => ["person"]
        ];
        $response = $this->json('POST', '/api/authenticate', $credentials);

        // dump($response);
        // $response->dump();

        $response->assertStatus(404);

        $response = json_decode($response->getContent(), true);
        $actual   = $response['message'];
        $expected = __('passwords.user');
        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function login_bad_password()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();

        $p = '123456.';

        $credentials = [
            'email' => $user->email,
            'password' => $p,
            'includes' => ["person"]
        ];
        $response = $this->json('POST', '/api/authenticate', $credentials);

        $response->assertStatus(422);

        $response = json_decode($response->getContent(), true);

        $actual   = $response['message'];
        $expected = __('auth.failed');
        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function login_inactive_user()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();
        // $u = $person->user;
        $user->disabled = true;
        $user->save();

        $p = '123456';

        $credentials = [
            'email' => $user->email,
            'password' => $p,
            'includes' => ["person"]
        ];
        $response = $this->json('POST', '/api/authenticate', $credentials);

        $response->assertStatus(422);

        $response = json_decode($response->getContent(), true);

        $actual   = $response['message'];
        $expected = __('auth.no.active');
        $this->assertEquals($expected, $actual);
    }
}
