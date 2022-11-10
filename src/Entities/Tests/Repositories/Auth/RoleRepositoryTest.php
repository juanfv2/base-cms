<?php

namespace Tests\Repositories\Auth;

use App\Models\Auth\Role;

use Juanfv2\BaseCms\Traits\ApiTestTrait;
use Tests\TestCase;

use Illuminate\Support\Facades\App;
use App\Repositories\Auth\RoleRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RoleRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    /**
     * @var RoleRepository
     */
    protected $modelRepository;

    public function setUp() : void
    {
        parent::setUp();
        $this->modelRepository = App::make(RoleRepository::class);
    }

    /** @test */
    public function repo_create_role()
    {
        $role = Role::factory()->make()->toArray();

        $createdRole = $this->modelRepository->create($role);

        $createdRole = $createdRole->toArray();
        $this->assertArrayHasKey('id', $createdRole);
        $this->assertNotNull($createdRole['id'], 'Created Role must have id specified');
        $this->assertNotNull(Role::find($createdRole['id']), 'Role with given id must be in DB');
        $this->assertModelData($role, $createdRole);
    }

    /** @test */
    public function repo_read_role()
    {
        $role = Role::factory()->create();

        $dbRole = $this->modelRepository->find($role->id);

        $dbRole = $dbRole->toArray();
        $this->assertModelData($role->toArray(), $dbRole);
    }

    /** @test */
    public function repo_update_role()
    {
        $role = Role::factory()->create();
        $fakeRole = Role::factory()->make()->toArray();

        $updatedRole = $this->modelRepository->update($role, $fakeRole);

        $this->assertModelData($fakeRole, $updatedRole->toArray());
        $dbRole = $this->modelRepository->find($role->id);
        $this->assertModelData($fakeRole, $dbRole->toArray());
    }

    /** @test */
    public function repo_delete_role()
    {
        $role = Role::factory()->create();

        $resp = $this->modelRepository->delete($role->id);

        $this->assertTrue($resp);
        $this->assertNull(Role::find($role->id), 'Role should not exist in DB');
    }
}
