<?php

namespace Tests\APIs\Auth;

use App\Models\Auth\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Juanfv2\BaseCms\Traits\ApiTestTrait;
use Tests\TestCase;

class ZForgotPasswordApiTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    /** @test */
    public function forgot_password()
    {
        // $this->withoutExceptionHandling();

        $account = User::factory()->create();

        $credentials = [
            'email' => $account->email,
        ];

        $this->response = $this->json('POST', route('api.password.email'), $credentials);

        $this->assertApiSuccess();

        $responseData = json_decode($this->response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $actual = $responseData['message'];
        $expected = __('passwords.sent');

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function email_not_found()
    {
        // $this->withoutExceptionHandling();

        $e = 'juanfv2@gmail.com';

        $credentials = [
            'email' => $e, //$account->email
        ];

        $this->response = $this->json('POST', route('api.password.email'), $credentials);

        $this->response->assertStatus(404);

        $response = json_decode($this->response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $actual = $response['message'];
        $expected = __('passwords.user');

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function forgot_email_expired_time()
    {
        $this->withoutExceptionHandling();

        $account = User::factory()->create();

        $credentials = [
            'email' => $account->email,
        ];

        $response = $this->json('POST', route('api.password.email'), $credentials);

        $response->assertOk();

        // validate time
        $response1 = $this->json('POST', route('api.password.email'), $credentials);

        // $response1->dump();

        $response = json_decode($response1->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // dd($response);

        $response1->assertStatus(401);

        $actual = $response['message'];
        $expected = __('passwords.throttled');

        $this->assertEquals($expected, $actual);
    }
}
