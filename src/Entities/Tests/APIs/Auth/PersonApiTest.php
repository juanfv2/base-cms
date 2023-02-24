<?php

namespace Tests\Feature\APIs\Auth;

use App\Models\Auth\Person;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Juanfv2\BaseCms\Traits\ApiTestTrait;
use Tests\TestCase;

class PersonApiTest extends TestCase
{
    use ApiTestTrait;
    use WithoutMiddleware;
    use DatabaseTransactions;
    // use RefreshDatabase;

    /** @test */
    public function api_index_people()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit = 10;
        $offset = 0;
        $models = Person::factory($created)->create();

        $this->response = $this->json('POST', route('api.users.store', ['limit' => $limit, 'offset' => $offset, 'to_index' => 2]));

        // $this->response->dd();

        $this->assertApiSuccess();
    }

    /** @test */
    public function api_create_person_without_image()
    {
        // $this->withoutExceptionHandling();

        $role = Role::factory()->create(['id' => 1]);
        $user = User::factory()->make()->toArray();
        $model = Person::factory()->make()->toArray();
        $required = [
            'withEntity' => 'auth_people',
            'password' => '123456',
            'password_confirmation' => '123456',
            'roles' => [$role->id],
            'role_id' => $role->id,
        ];
        $model = array_merge($model, $user, $required);

        $this->response = $this->actingAsAdmin()->json('POST', route('api.users.store'), $model);

        // $this->response->dd();

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_create_person_with_image()
    {
        // $this->withoutExceptionHandling();

        $role = Role::factory()->create(['id' => 1]);
        $user = User::factory()->make()->toArray();
        $model = Person::factory()->make()->toArray();
        // Storage::fake('assets');

        $rCountry = 'td';
        $file = UploadedFile::fake()->image('avatar.jpg');

        $required = [
            'withEntity' => 'auth_people',
            'password' => '123456',
            'password_confirmation' => '123456',
            'roles' => [$role->id],
            'role_id' => $role->id,
            'photo' => $file,
        ];
        $model = array_merge($model, $user, $required);

        $this->response = $this->json('POST', route('api.users.store'), $model, ['r-country' => $rCountry]);

        // $this->response->dump();
        $responseContent = $this->response->json();

        $iPath = "assets/adm/$rCountry/auth_users/photo/{$responseContent['data']['photo']['name']}";

        // logger(__FILE__ . ':' . __LINE__ . ' $this->responseContent, $file->hashName() ', [$this->responseContent, $file->hashName()]);
        // dd($this->responseContent, $file->hashName());

        $this->assertIsNumeric($responseContent['data']['photo']['entity_id']);
        $this->assertIsNumeric($responseContent['data']['photo']['id']);

        Storage::disk('public')->assertExists($iPath);
    }

    /** @test */
    public function api_read_person()
    {
        $model = Person::factory()->create();

        $this->response = $this->actingAsAdmin()->json('GET', route('api.users.show', ['user' => $model->user_id]));

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_update_person()
    {
        $role = Role::factory()->create();
        $model = Person::factory()->create();
        $modelEdited = [
            // table
            'withEntity' => 'auth_people',

            // user
            'email' => 'antonette30@ebert.com',   // fk
            'name' => 'est',
            'disabled' => 1,
            'user_id' => $model->user_id,
            'role_id' => $role->id,

            // person
            'firstName' => 'est',
            'lastName' => 'ut',
            'cellPhone' => '1-873-322-7732 x9420',
            'birthDate' => '2011-09-21',
            'address' => '3331 Torre Valleys Suite 807 Abigail, FL 78513',
            'neighborhood' => 'esse',

        ];

        $this->response = $this->actingAsAdmin()->json('PUT', route('api.users.update', ['user' => $model->user_id]), $modelEdited);

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_delete_person()
    {
        $model = Person::factory()->create();

        $this->response = $this->actingAsAdmin()->json('DELETE', route('api.users.destroy', ['user' => $model->user_id]));

        $this->assertApiSuccess();

        $this->response = $this->actingAsAdmin()->json('DELETE', route('api.users.destroy', ['user' => $model->user_id]));

        $this->response->assertStatus(404);
    }
}
