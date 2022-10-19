<?php

namespace Tests\APIs\Auth;

use Tests\TestCase;
use App\Models\Driver;
use Tests\ApiTestTrait;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserDriverApiTest extends TestCase
{
    use ApiTestTrait,
        WithoutMiddleware,
        DatabaseTransactions
        // RefreshDatabase
        // ...
    ;

    /** @test */
    public function api_create_driver_without_image()
    {
        // $this->withoutExceptionHandling();

        $role     = Role::factory()->create();
        $user     = User::factory()->make()->toArray();
        $driver   = Driver::factory()->make()->toArray();
        $required = [
            'withEntity'            => 'drivers',
            'password'              => '123456',
            'password_confirmation' => '123456',
            'imei'                  => '123456',
            'roles'                 => [$role->id],
            'role_id'               => $role->id
        ];
        $driver = array_merge($driver, $user, $required);

        unset($driver['user_id']); // sin "user_id"
        // dd(json_encode($driver));

        $this->response = $this->actingAsAdmin('api')->json('POST', route('api.users.store'), $driver);

        // $this->response->dump();
        // dd($this->response->json());

        $this->assertJsonModifications();
    }
    /** @test */
    public function api_create_driver_with_image()
    {
        // $this->withoutExceptionHandling();

        $role   = Role::factory()->create(['id' => 4]);
        $user   = User::factory()->make()->toArray();
        $driver = Driver::factory()->make()->toArray();
        unset($driver['user_id']); // sin "user_id"
        // dd(json_encode($driver) );
        // Storage::fake('assets');

        $rCountry = 'td';
        $file = UploadedFile::fake()->image('avatar.jpg');

        $required = [
            'withEntity'            => 'drivers',
            'password'              => '123456',
            'password_confirmation' => '123456',
            'roles'                 => [$role->id],
            'role_id'               => $role->id,
            'photo'              => $file,
        ];
        $driver  = array_merge($driver,  $user, $required);


        $this->response = $this->json('POST', route('api.users.store'), $driver, ['r-country' => $rCountry]);

        // $this->response->dump();
        $this->responseContent = $this->response->json();

        $iPath = "assets/adm/$rCountry/auth_users/photo/{$this->responseContent['data']['photo']['name']}";

        // logger(__FILE__ . ':' . __LINE__ . ' $this->responseContent, $file->hashName() ', [$this->responseContent, $file->hashName()]);
        // dd($this->responseContent, $file->hashName());

        $this->assertIsNumeric($this->responseContent['data']['photo']['entity_id']);
        $this->assertIsNumeric($this->responseContent['data']['photo']['id']);
        Storage::disk('public')->assertExists($iPath);
    }

    /** @test */
    public function api_read_driver()
    {
        $driver = Driver::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('GET', route('api.users.show',  ['user' => $driver->id]));

        // dd($this->response->json(), $model->user);
        // dd($this->response->json());

        // $user = User::find($model->id);
        // $this->assertJsonShow($user);

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_update_driver()
    {
        // $this->withoutExceptionHandling();

        $driver = Driver::factory()->create();
        $role   = Role::factory()->create(['id' => 3]);

        // $editedAccount = Account::factory()->make()->toArray();
        $editedAccount = array(
            // table
            'withEntity'            => 'drivers',

            // user
            'email'    => 'antonette30@ebert.com',   // fk
            'name'     => 'est',
            'disabled' => 1,
            'user_id'  => $driver->user_id,
            'role_id'  => $role->id,

            // driver
            'firstName'    => 'est',
            'lastName'     => 'ut',
            'imei'         => $driver->imei,
            'cellPhone'    => '1-873-322-7732 x9420',
            'birthDate'    => '2011-09-21',
            'address'      => '3331 Torrey Valleys Suite 807 Abigailstad, FL 78513',
            'neighborhood' => 'esse',


        );

        $this->response = $this->actingAsAdmin('api')->json('PUT', route('api.users.update',  ['user' => $driver->user_id]), $editedAccount);

        // dd($this->response->json());

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_delete_driver()
    {
        $driver = Driver::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('DELETE', route('api.users.destroy',  ['user' => $driver->user_id,]));

        $this->assertApiSuccess();

        $this->response = $this->actingAsAdmin('api')->json('GET', route('api.users.show', ['user' => $driver->user_id]));

        $this->response->assertStatus(404);
    }
}
