<?php

namespace Tests\APIs\Auth;

use App\Models\Auth\Account;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Juanfv2\BaseCms\Traits\ApiTestTrait;
use Tests\TestCase;

class UserAccountApiTest extends TestCase
{
    use ApiTestTrait;
    use WithoutMiddleware;
    use DatabaseTransactions;
    // use RefreshDatabase;

    /** @test */
    public function api_create_account_without_image()
    {
        // $this->withoutExceptionHandling();

        $role = Role::factory()->create();
        $user = User::factory()->make()->toArray();
        $account = Account::factory()->make()->toArray();
        $required = [
            'withEntity' => 'auth_accounts',
            'password' => '123456',
            'password_confirmation' => '123456',
            'roles' => [$role->id],
            'role_id' => $role->id,
        ];
        $account = array_merge($account, $user, $required);

        unset($account['user_id']); // sin "user_id"
        // dd(json_encode($account));

        $this->response = $this->actingAsAdmin('api')->json('POST', route('api.users.store'), $account);

        // $this->response->dump();
        // dd($this->response->json());

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_create_account_with_image()
    {
        // $this->withoutExceptionHandling();

        $role = Role::factory()->create(['id' => 3]);
        $user = User::factory()->make()->toArray();
        $account = Account::factory()->make()->toArray();
        unset($account['user_id']); // sin "user_id"
        // dd(json_encode($account) );
        // Storage::fake('assets');

        $rCountry = 'td';
        $file = UploadedFile::fake()->image('avatar.jpg');

        $required = [
            'withEntity' => 'auth_accounts',
            'password' => '123456',
            'password_confirmation' => '123456',
            'roles' => [$role->id],
            'role_id' => $role->id,
            'photo' => $file,
        ];
        $account = array_merge($account, $user, $required);

        $this->response = $this->json('POST', route('api.users.store'), $account, ['r-country' => $rCountry]);

        // $this->response->dump();
        $responseContent = $this->response->json();

        $iPath = "assets/adm/$rCountry/auth_users/photo/{$responseContent['data']['photo']['name']}";

        // logger(__FILE__ . ':' . __LINE__ . ' $responseContent, $file->hashName() ', [$responseContent, $iPath, $file->hashName()]);
        // dd($responseContent, $file->hashName());

        $this->assertIsNumeric($responseContent['data']['photo']['entity_id']);
        $this->assertIsNumeric($responseContent['data']['photo']['id']);
        Storage::disk('public')->assertExists($iPath);
    }

    /** @test */
    public function api_read_account()
    {
        $account = Account::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('GET', route('api.users.show', ['user' => $account->user_id]));

        // dd($this->response->json(), $model->user);
        // dd($this->response->json());

        // $user = User::find($model->id);
        // $this->assertJsonShow($user);

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_update_account()
    {
        // $this->withoutExceptionHandling();

        $account = Account::factory()->create();
        $role = Role::factory()->create(['id' => 3]);

        // $editedAccount = Account::factory()->make()->toArray();
        $editedAccount = [
            // table
            'withEntity' => 'auth_accounts',

            // user
            'email' => 'antonette30@ebert.com',   // fk
            'name' => 'est',
            'disabled' => 1,
            'user_id' => $account->user_id,
            'role_id' => $role->id,

            // account
            'firstName' => 'est',
            'lastName' => 'ut',
            'imei' => $account->imei,
            'cellPhone' => '1-873-322-7732 x9420',
            'birthDate' => '2011-09-21',
            'address' => '3331 Torrey Valleys Suite 807 Abigailstad, FL 78513',
            'neighborhood' => 'esse',

        ];

        $this->response = $this->actingAsAdmin('api')->json('PUT', route('api.users.update', ['user' => $account->user_id]), $editedAccount);

        // dd($this->response->json());

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_delete_account()
    {
        $account = Account::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('DELETE', route('api.users.destroy', ['user' => $account->user_id]));

        $this->assertApiSuccess();

        $this->response = $this->actingAsAdmin('api')->json('GET', route('api.users.show', ['user' => $account->user_id]));

        $this->response->assertStatus(404);
    }
}
