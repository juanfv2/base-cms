<?php

namespace Tests\APIs\Auth;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;

use Tests\TestCase;
use Tests\ApiTestTrait;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RoleApiTest extends TestCase
{
    use ApiTestTrait,
        WithoutMiddleware,
        DatabaseTransactions
        // RefreshDatabase
        // ..
    ;

    /** @test */
    public function api_index_role()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit   = 10;
        $offset  = 0;
        $areas   = Role::factory($created)->create();

        $this->response = $this->json('POST', route('api.roles.store', ['limit' => $limit, 'offset' => $offset, 'to_index' => 2]));

        // $this->response->dump();
        // dd($this->response->json());

        $this->assertJsonIndex($limit, $areas[0]);
    }

    /** @test */
    public function api_create_role()
    {
        // $this->withoutExceptionHandling();

        $permissions = Permission::factory(3)->create()->pluck('id')->toArray();
        $role = Role::factory()->make()->toArray();
        $role['permissions'] = $permissions;

        $this->response = $this->actingAsAdmin('api')->json('POST', route('api.roles.store'), $role);

        // $this->response->dump();
        // dd($this->response->json());

        $this->responseContent = $this->response->json();
        $rolePermissions = [];

        foreach ($permissions as $key) {
            $rolePermissions[] = [
                'role_id' => $this->responseContent['data']['id'],
                'permission_id' => $key
            ];
        }
        $this->assertDatabaseCount('auth_roles_has_permissions', 3);
        $this->assertDatabaseHas('auth_roles_has_permissions', $rolePermissions[0]);

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_read_role()
    {
        $model = Role::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('GET', route('api.roles.show', ['role' => $model->id]));

        $this->assertJsonShow($model);
    }

    /** @test */
    public function api_update_role()
    {
        $role = Role::factory()->create();
        $editedRole = Role::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('PUT', route('api.roles.update', ['role' => $role->id]), $editedRole);

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_delete_role()
    {
        $role = Role::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('DELETE', route('api.roles.destroy', ['role' => $role->id]));

        $this->assertApiSuccess();

        $this->response = $this->actingAsAdmin('api')->json('GET', route('api.roles.show', ['role' => $role->id]));

        $this->response->assertStatus(404);
    }
}
