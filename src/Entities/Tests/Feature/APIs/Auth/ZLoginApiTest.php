<?php

namespace Tests\Feature\APIs\Auth;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Juanfv2\BaseCms\Traits\ApiTestTrait;
use Tests\TestCase;

class ZLoginApiTest extends TestCase
{
    use ApiTestTrait;

    // use WithoutMiddleware;
    use DatabaseTransactions;
    // use RefreshDatabase;

    /** @test */
    public function login_user_person()
    {
        $this->withoutExceptionHandling();
        // $this->artisan('passport:install');

        $user = User::factory()->create();
        $p = '123456';
        $credentials = [
            'email' => $user->email,
            'password' => $p,
            'includes' => ['person'],
        ];

        $this->response = $this->json('POST', route('api.login.login'), $credentials);

        // $this->response->dd();

        $this->assertJsonModifications();
    }

    /** @test */
    public function logout_user()
    {
        $this->withoutExceptionHandling();
        // $this->artisan('passport:install');

        $user = User::factory()->create(['disabled' => 0]);
        $p = '123456';
        $credentials = [
            'email' => $user->email,
            'password' => $p,
            'includes' => ['person', 'token'],
        ];

        $this->response = $this->json('POST', route('api.login.login'), $credentials);

        $this->assertApiSuccess();
        $responseContent = $this->response->json();

        // dd($responseContent);

        $token = $responseContent['data']['token'];

        $this->response = $this->actingAsAdmin()->json('POST', route('api.login.logout'), [], ['Authorization' => 'Bearer '.$token]);
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
            'includes' => ['person'],
        ];
        $this->response = $this->json('POST', route('api.login.login'), $credentials);

        $this->response->assertStatus(404);

        $responseContent = $this->response->json();
        $actual = $responseContent['message'];
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
            'includes' => ['person'],
        ];
        $this->response = $this->json('POST', route('api.login.login'), $credentials);

        $this->response->assertStatus(422);

        $responseContent = $this->response->json();
        $actual = $responseContent['message'];
        $expected = __('auth.failed');

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function login_inactive_user()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create(['disabled' => 1]);
        $p = '123456';
        $credentials = [
            'email' => $user->email,
            'password' => $p,
            'includes' => ['person'],
        ];
        $this->response = $this->json('POST', route('api.login.login'), $credentials);

        $this->response->assertStatus(422);

        $responseContent = $this->response->json();
        $actual = $responseContent['message'];
        $expected = __('auth.no.active');

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function get_users_not_allow_role_permissions()
    {
        $created = 3;
        $limit = 2;
        $offset = 1;
        $users = User::factory($created)->create();

        $this->response = $this->actingAsAdmin()->json('POST', route('api.users.store', ['limit' => $limit, 'offset' => $offset, 'to_index' => 2]));

        // $this->response->dd();

        $this->response->assertStatus(401);
    }

    /** @test */
    public function get_users_allow_role_permissions()
    {
        $role = Role::factory()->create(['id' => 1]);
        $permission = Permission::factory()->create(['id' => 1, 'urlBackEnd' => 'api.users.store']);
        $user = User::factory()->create(['disabled' => 0]);

        $role->permissions()->attach([$permission->id]);
        $user->roles()->attach([$role->id]);

        $p = '123456';
        $credentials = [
            'email' => $user->email,
            'password' => $p,
            'includes' => ['person', 'token'],
        ];

        $this->response = $this->json('POST', route('api.login.login'), $credentials);

        $this->assertApiSuccess();
        $responseContent = $this->response->json();

        // dd($responseContent);

        $token = $responseContent['data']['token'];

        $created = 3;
        $limit = 2;
        $offset = 1;
        $users = User::factory($created)->create();

        $this->response = $this->json(
            'POST',
            route('api.users.store', ['limit' => $limit, 'offset' => $offset, 'to_index' => 2]),
            [],
            ['Authorization' => 'Bearer '.$token]

        );

        // $this->response->dd();

        $this->response->assertStatus(200);
    }
}
