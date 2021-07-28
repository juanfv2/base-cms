<?php

namespace Tests\APIs\Auth;

use App\Models\Auth\Person;
use App\Models\Auth\Role;
use App\Models\Auth\User;

use Tests\TestCase;
use Tests\ApiTestTrait;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PersonApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions; // , RefreshDatabase

    /** @test */
    public function api_create_person()
    {
        // $this->withoutExceptionHandling();

        $role    = Role::factory()->create(['id' => 1]);
        $user    = User::factory()->make()->toArray();
        $person  = Person::factory()->make()->toArray();
        $required = [
            'withEntity'            => 'auth_people',
            'password'              => '123456',
            'password_confirmation' => '123456',
            'roles'                 => [$role->id],
            'role_id'               => $role->id
        ];
        $person  = array_merge($person,  $user, $required);

        unset($person['user_id']); // sin "user_id"
        $this->response = $this->actingAsAdmin('api')->json('POST', '/api/users', $person);
        $this->getContent();
        // dd($this->responseContent);
        // $this->response->dump();

        $this->assertApiModifications($person);
    }

    /** @test */
    public function api_read_person()
    {
        $person = Person::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('GET', "/api/users/{$person->user_id}");

        $user = User::find($person->user_id)->toArray();
        $this->assertApiResponse($user);
    }

    /** @test */
    public function api_update_person()
    {
        $role    = Role::factory()->create(['id' => 1]);
        $person = Person::factory()->create();
        $editedPerson = Person::factory(
            [
                'withEntity' => 'auth_people',
                'role_id'    => $role->id,
                'user_id'    => $person->user_id,
                'email'      => 'antonette30@ebert.com',   // fk
                'name'       => 'est',
                'disabled'   => 1,
            ]
        )->make()->toArray();
        // dd($editedPerson);
        $this->response = $this->actingAsAdmin('api')->json('PUT', "/api/users/{$person->user_id}", $editedPerson);

        $this->getContent();
        // dd($this->responseContent);
        $this->assertApiModifications($editedPerson);
    }

    /** @test */
    public function api_delete_person()
    {
        $person = Person::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('DELETE', "/api/users/{$person->user_id}?withEntity=auth_people");

        $this->assertApiSuccess();

        $this->response = $this->actingAsAdmin('api')->json('GET', "/api/users/{$person->user_id}");

        $this->response->assertStatus(404);
    }
}
