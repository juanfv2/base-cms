<?php

namespace Tests\APIs\Auth;

use App\Models\Auth\Role;
use App\Models\Auth\Permission;

use Tests\TestCase;
use Tests\ApiTestTrait;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RoleApiTest extends TestCase
{
    use ApiTestTrait,
        WithoutMiddleware,
        // DatabaseTransactions
        RefreshDatabase
        // ...
    ;

    /** @test */
    public function api_create_role()
    {
        // $this->withoutExceptionHandling();

        $permissions = Permission::factory(3)->create()->pluck('id')->toArray();
        $role = Role::factory()->make()->toArray();
        $role['permissions'] = $permissions;

        $this->response = $this->actingAsAdmin('api')->json('POST', route('api.roles.store'), $role);

        $this->getContent();

        $rolePermissions = [];

        foreach ($permissions as $key) {
            $rolePermissions[] = [
                'role_id' => $this->responseContent['data']['id'],
                'permission_id' => $key
            ];
        }
        $this->assertDatabaseCount('auth_roles_has_permissions', 3);
        $this->assertDatabaseHas('auth_roles_has_permissions', $rolePermissions[0]);

        // $this->response->dump();

        $this->assertApiModifications($role);
    }

    /** @test */
    public function api_read_role()
    {
        $role = Role::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('GET', route('api.roles.show', ['role' => $role->id]));

        $this->assertApiResponse($role->toArray());
    }

    /** @test */
    public function api_update_role()
    {
        $role = Role::factory()->create();
        $editedRole = Role::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('PUT', route('api.roles.update', ['role' => $role->id]), $editedRole);

        $this->assertApiModifications($editedRole);
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
