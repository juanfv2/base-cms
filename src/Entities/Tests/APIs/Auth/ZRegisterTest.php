<?php

namespace Tests\APIs\Auth;

use Tests\TestCase;
use Tests\ApiTestTrait;

use App\Models\Auth\Role;
use App\Models\Auth\User;

use App\Models\Auth\XUserVerified;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ZRegisterTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    /** @test */
    public function create_an_account()
    {
        $this->withoutExceptionHandling();

        $role    = Role::factory()->create(['id' => 3]);
        $p = '123456';

        $credentials = [
            'email' => 'juanfv2@gmail.com',
            'name' => 'juanfv2',
            'firstName' => 'juanfv2',
            'lastName' => 'juanfv2',
            'password' => $p,
            'password_confirmation' => $p,
            'role_id' => 3
        ];

        // Artisan::call('passport:install', ['-vvv' => true]);
        // Artisan::call('db:seed', ['-vvv' => true]);

        $response1 = $this->json('POST', '/api/register', $credentials);

        $response = json_decode($response1->getContent(), true);
        // dd($response);
        // dump($response);
        // $response->dump();

        $response1->assertOk();
    }

    /** @test */
    public function verify_account()
    {
        $this->withoutExceptionHandling();

        $role    = Role::factory()->create(['id' => 3]);
        $p = '123456';
        $e = 'juanfv2@gmail.com';

        $credentials = [
            'email' => $e,
            'name' => 'juanfv2',
            'firstName' => 'juanfv2',
            'lastName' => 'juanfv2',
            'password' => $p,
            'password_confirmation' => $p,
        ];

        $response = $this->json('POST', '/api/register', $credentials);

        $response->assertOk();

        $user = User::where('email', $e)->first();
        $userV = XUserVerified::where('user_id', $user->id)->first();

        $response = $this->json('POST', '/api/user/verify/' . $userV->token);

        // dump($response);
        // $response->dump();

        $response->assertOk();
    }
}
