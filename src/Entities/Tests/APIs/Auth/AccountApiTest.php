<?php

namespace Tests\APIs\Auth;

use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Auth\Role;

use App\Models\Auth\User;
use App\Models\Auth\Account;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AccountApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, RefreshDatabase;

    /** @test */
    public function api_create_account()
    {
        // $this->withoutExceptionHandling();

        $role    = Role::factory()->create(['id' => 3]);
        $user    = User::factory()->make()->toArray();
        $account = Account::factory()->make()->toArray();
        $required = [
            'withEntity'            => 'auth_accounts',
            'password'              => '123456',
            'password_confirmation' => '123456',
            'roles'                 => [$role->id],
            'role_id'               => $role->id
        ];
        $account = array_merge($account, $user, $required);

        unset($account['user_id']); // sin "user_id"

        $this->response = $this->actingAsAdmin('api')->json('POST', '/api/users', $account);

        // $this->response->dump();

        $this->assertApiModifications($account);
    }

    /** @test */
    public function api_read_account()
    {
        $account = Account::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('GET', "/api/users/{$account->user_id}");

        $user = User::find($account->user_id)->toArray();
        $this->assertApiResponse($user);
    }

    /** @test */
    public function api_update_account()
    {
        $account = Account::factory()->create();
        $role    = Role::factory()->create(['id' => 3]);
        $editedAccount = Account::factory(
            [
                'withEntity' => 'auth_accounts',
                'role_id'    => $role->id,
                'user_id'    => $account->user_id,
                'email'      => 'antonette30@ebert.com',   // fk
                'name'       => 'est',
                'disabled'   => 1,
            ]
        )->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('PUT', "/api/users/{$account->user_id}", $editedAccount);

        $this->assertApiModifications($editedAccount);
    }

    /** @test */
    public function api_delete_account()
    {
        $account = Account::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('DELETE', "/api/users/{$account->user_id}?withEntity=auth_accounts");

        $this->assertApiSuccess();

        $this->response = $this->actingAsAdmin('api')->json('GET', "/api/users/{$account->user_id}");

        $this->response->assertStatus(404);
    }
}
