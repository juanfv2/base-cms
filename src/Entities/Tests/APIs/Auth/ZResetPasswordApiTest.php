<?php

namespace Tests\APIs\Auth;

use App\Models\Auth\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Juanfv2\BaseCms\Traits\ApiTestTrait;
use Tests\TestCase;

class ZResetPasswordApiTest extends TestCase
{
    use ApiTestTrait;
    use WithoutMiddleware;
    use DatabaseTransactions;
    // use RefreshDatabase;

    /** @test */
    public function reset_password()
    {
        $this->withoutExceptionHandling();

        $account = User::factory()->create();

        $credentials = [
            'email' => $account->email,
        ];

        $response = $this->json('POST', route('api.password.email'), $credentials);

        $response->assertOk();

        $resetTable = 'password_resets';
        $reset = DB::table($resetTable)->where('email', $account->email)->first();

        $credentials = [
            'token' => $reset->token,
            'email' => $account->email,
            'password' => 'secret1',
            'password_confirmation' => 'secret1',
        ];

        $response1 = $this->json('POST', route('api.password.reset'), $credentials);

        // $response1->dump();

        $response = json_decode($response1->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // dd($response);

        $response1->assertOk();
        $response1->assertJson(['message' => __('passwords.reset')]);
    }

    /** @test */
    public function email_not_found_in_password_reset_table()
    {
        $this->withoutExceptionHandling();

        $e = 'juanfv2@gmail.com';
        $account = User::factory()->create();

        $credentials = [
            'email' => $account->email,
        ];

        $response = $this->json('POST', route('api.password.email'), $credentials);

        $response->assertOk();

        $credentials = [
            'token' => 'demo',
            'email' => $e,
            'password' => 'secret1',
            'password_confirmation' => 'secret1',
        ];

        $response1 = $this->json('POST', route('api.password.reset'), $credentials);

        $response = json_decode($response1->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // dd($response);

        $response1->assertStatus(404);
        $response1->assertJson(['message' => __('passwords.user')]);
    }

    /** @test */
    public function bad_password_in_password_reset_table()
    {
        // $this->withoutExceptionHandling();

        $e = 'juanfv2@gmail.com';
        $account = User::factory()->create();

        $credentials = [
            'email' => $account->email,
        ];

        $response = $this->json('POST', route('api.password.email'), $credentials);

        $response->assertOk();

        $resetTable = 'password_resets';
        $reset = DB::table($resetTable)->where('email', $account->email)->first();

        $credentials = [
            'token' => $reset->token,
            'email' => $account->email,
            'password' => 'secret1',
            'password_confirmation' => 'secret2',
        ];

        $response1 = $this->json('POST', route('api.password.reset'), $credentials);

        // dump($response1);
        // $response1->dump();

        $response = json_decode($response1->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // dd($response);

        $response1->assertStatus(422);
        $response1->assertJsonValidationErrors('password');
    }
}
