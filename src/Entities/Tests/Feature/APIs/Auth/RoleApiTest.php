<?php

namespace Tests\Feature\APIs\Auth;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Juanfv2\BaseCms\Traits\ApiTestTrait;
use Tests\TestCase;

class RoleApiTest extends TestCase
{
    use ApiTestTrait;
    use DatabaseTransactions;
    use WithoutMiddleware;
    // use RefreshDatabase;

    /** @test */
    public function api_index_roles()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit = 10;
        $offset = 0;
        $models = Role::factory($created)->create();

        $this->response = $this->json('POST', route('api.roles.store', ['limit' => $limit, 'offset' => $offset, 'to_index' => 2]));

        // $this->response->dd();

        $this->assertJsonIndex($limit, $models[0]);
    }

    /** @test */
    public function api_create_role()
    {
        // $this->withoutExceptionHandling();

        $permissions = Permission::factory(3)->create()->pluck('id')->toArray();
        $role = Role::factory()->make()->toArray();
        $role['permissions'] = $permissions;

        $this->response = $this->actingAsAdmin()->json('POST', route('api.roles.store'), $role);

        // $this->response->dump();
        // dd($this->response->json());

        $responseContent = $this->response->json();
        $rolePermissions = [];

        foreach ($permissions as $key) {
            $rolePermissions[] = [
                'role_id' => $responseContent['data']['id'],
                'permission_id' => $key,
            ];
        }
        $this->assertDatabaseCount('auth_permission_role', 3);
        $this->assertDatabaseHas('auth_permission_role', $rolePermissions[0]);

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_read_role()
    {
        $model = Role::factory()->create();

        $this->response = $this->actingAsAdmin()->json('GET', route('api.roles.show', ['role' => $model->id]));

        $this->assertJsonShow($model);
    }

    /** @test */
    public function api_update_role()
    {
        $model = Role::factory()->create();
        $modelEdited = Role::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin()->json('PUT', route('api.roles.update', ['role' => $model->id]), $modelEdited);

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_delete_role()
    {
        $model = Role::factory()->create();

        $this->response = $this->actingAsAdmin()->json('DELETE', route('api.roles.destroy', ['role' => $model->id]));

        $this->assertApiSuccess();

        $this->response = $this->actingAsAdmin()->json('DELETE', route('api.roles.destroy', ['role' => $model->id]));

        $this->response->assertStatus(404);
    }
}
