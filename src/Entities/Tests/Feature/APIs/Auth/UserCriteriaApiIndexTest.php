<?php

namespace Tests\Feature\APIs\Auth;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Schema;
use Juanfv2\BaseCms\Traits\ApiTestTrait;
use Tests\TestCase;

class UserCriteriaApiIndexTest extends TestCase
{
    use ApiTestTrait;
    use DatabaseTransactions;
    use WithoutMiddleware;
    // use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Schema::disableForeignKeyConstraints();
        Role::query()->forceDelete();
        User::query()->forceDelete();
        Schema::enableForeignKeyConstraints();
    }

    /** @test */
    public function api_index_limit_users()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit = 10;
        $offset = 2;
        $users = User::factory($created)->create();

        $this->response = $this->actingAsAdmin()->json('POST', route('api.users.store', ['limit' => $limit, 'offset' => $offset, 'to_index' => 2]));

        // $this->response->dump();
        // dd($this->response->json(), $user);

        $this->assertJsonIndex($limit, $users[$offset]);
    }

    /** @test */
    public function api_index_order_by_users()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit = 2;
        $founded = 2;
        $areas = User::factory($created)->create();
        $user = User::factory()->create(['name' => 'a-nina']);
        $user = User::factory()->create(['name' => 'b-nina']);
        $conditions = '[{"c":"AND auth_users.name like","v":"nina"}]';
        $sorts = '[{"field":"auth_users.name","order":-1}]';

        // static desc = -1
        // static asc = 1

        $this->response = $this->json('POST', route('api.users.store', [
            'conditions' => $conditions,
            'limit' => $limit,
            'sorts' => $sorts,
            'to_index' => 2,
        ]));

        // $this->response->dump();

        // dd($this->response->json());

        $this->assertJsonIndex($founded, $user);
    }

    /** @test */
    public function api_index_only_trashed_users()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit = 10;
        $founded = 2;
        $areas = User::factory($created)->create();
        $user = User::factory()->create(['name' => 'a-nina'])->delete();
        $user = User::factory()->create(['name' => 'b-nina']);
        $user->delete();
        $sorts = '[{"field":"auth_users.id","order":-1}]';
        $trashed = 1;

        // static desc = -1
        // static asc = 1

        $this->response = $this->json('POST', route('api.users.store', [
            // 'conditions' => $conditions,
            'limit' => $limit,
            'sorts' => $sorts,
            'to_index' => 2,
            'trashed' => $trashed,
        ]));

        // $this->response->dump();

        // dd($this->response->json());

        $this->assertJsonIndex($founded, $user);
    }

    /** @test */
    public function api_index_2_with_users()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit = 10;
        $founded = 2;
        $users = User::factory($created)->create();
        $user = User::factory()->create(['name' => '-bo-nina']);
        $user = User::factory()->create(['name' => '-sv-nina']);
        $conditions = '[{"c":"AND auth_users.name like","v":"-bo-"},{"c":"OR auth_users.name like","v":"-sv-"}]';
        $sorts = '[{"field":"auth_users.name","order":-1}]';
        $with = '["role", "roles"]';
        // static desc = -1
        // static asc = 1
        // $user->hidden[] = 'role_id'; if company_id is not in the select fields, don't work
        $user->setAttribute('role', $user->role);

        $this->response = $this->json('POST', route('api.users.store', [
            'conditions' => $conditions,
            'limit' => $limit,
            'sorts' => $sorts,
            'with' => $with,
            'to_index' => 2,
        ]));

        // $this->response->dump();

        // dd($this->response->json());

        $this->assertJsonIndex($founded, $user);
    }

    /** @test */
    public function api_index_2_with_count_users()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit = 10;
        $founded = 2;
        $users = User::factory($created)->create();
        $user = User::factory()->create(['name' => '-bo-nina']);
        $user = User::factory()->create(['name' => '-sv-nina']);
        $conditions = '[{"c":"AND auth_users.name like","v":"-bo-"},{"c":"OR auth_users.name like","v":"-sv-"}]';
        $sorts = '[{"field":"auth_users.name","order":-1}]';
        $sorts = '[{"field":"auth_users.name","order":-1}]';
        $select = 'auth_users.id,auth_users.name';
        $withCount = '["roles"]';
        // static desc = -1
        // static asc  = 1
        // $user->hidden[] = 'company_id';
        $user->setAttribute('roles_count', 0);

        $this->response = $this->json('POST', route('api.users.store', [
            'conditions' => $conditions,
            'limit' => $limit,
            'sorts' => $sorts,
            // 'select'     => $select,
            'withCount' => $withCount,
            'to_index' => 2,
        ]));

        // $this->response->dump();
        // dd($this->response->json());

        $this->assertJsonIndex($founded, $user);
    }

    /** @test */
    public function api_index_2_select_fields_users()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit = 10;
        $founded = 2;
        $users = User::factory($created)->create();
        $user = User::factory()->create(['name' => '-bo-nina']);
        $user = User::factory()->create(['name' => '-sv-nina']);
        $conditions = '[{"c":"AND auth_users.name like","v":"-bo-"},{"c":"OR auth_users.name like","v":"-sv-"}]';
        $sorts = '[{"field":"auth_users.name","order":-1}]';
        $sorts = '[{"field":"auth_users.name","order":-1}]';
        $select = 'auth_users.id,auth_users.name';
        // static desc = -1
        // static asc = 1
        $user->hidden[] = 'email';
        $user->hidden[] = 'uid';
        $user->hidden[] = 'email_verified_at';
        $user->hidden[] = 'disabled';
        $user->hidden[] = 'userCanDownload';
        $user->hidden[] = 'phoneNumber';
        $user->hidden[] = 'disabled';
        $user->hidden[] = 'group_id';
        $user->hidden[] = 'role_id';
        $user->hidden[] = 'country_id';

        $this->response = $this->json('POST', route('api.users.store', [
            'conditions' => $conditions,
            'limit' => $limit,
            'sorts' => $sorts,
            'select' => $select,
            'to_index' => 2,
        ]));

        // $this->response->dump();

        // dd($this->response->json());

        $this->assertJsonIndex($founded, $user);
    }

    /** @test */
    public function api_index_joins_users()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit = 10;
        $founded = 2;
        $user = User::factory($created)->create();
        $user = User::factory()->create(['name' => '-bo-nina']);
        $user = User::factory()->create(['name' => '-sv-nina']);
        $conditions = '[{"c":"AND auth_users.name like","v":"-bo-"},{"c":"OR auth_users.name like","v":"-sv-"}]';
        $sorts = '[{"field":"auth_users.name","order":-1}]';
        $joins = '[{"c":"auth_roles.id.role_id","v":["auth_roles.name as roleName"]}]';

        $user->setAttribute('roleName', $user->role->name);
        // joins: [{"c":"companies.id.company_id","v":["companies.name as companyName"]}]
        // static desc = -1
        // static asc = 1

        $this->response = $this->json('POST', route('api.users.store', [
            'conditions' => $conditions,
            'limit' => $limit,
            'sorts' => $sorts,
            'joins' => $joins,
            'to_index' => 2,
        ]));

        // $this->response->dump();

        // dd($this->response->json());

        $this->assertJsonIndex($founded, $user);
    }

    /** @test */
    public function api_index_1_condition_users()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit = 10;
        $founded = 1;
        $founded = 1;
        $user = User::factory($created)->create();
        $user = User::factory()->create(['name' => '-nana-']);

        $conditions = '[{"c":"AND auth_users.name like","v":"-nana-"}]';
        // ['c' => 'AND auth_users.name like', 'v' => 'nana'];

        $this->response = $this->json('POST', route('api.users.store', [
            'conditions' => $conditions,
            'limit' => $limit,
            'to_index' => 2,
        ]));

        // $this->response->dump();

        // dd($this->response->json(), $areas[$founded]->getAttributes(), $areas[$founded]->getHidden());

        $this->assertJsonIndex($founded, $user);
    }

    /** @test */
    public function api_index_2_conditions_users()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit = 10;
        $founded = 2;
        $areas = User::factory($created)->create();
        $user = User::factory()->create(['name' => '-bo-nina']);
        $user = User::factory()->create(['name' => '-sv-nina']);
        $conditions = '[{"c":"AND auth_users.name like","v":"-bo-"},{"c":"OR auth_users.name like","v":"-sv-"}]';
        $sorts = '[{"field":"auth_users.name","order":-1}]';

        // static desc = -1
        // static asc = 1

        $this->response = $this->json('POST', route('api.users.store', [
            'conditions' => $conditions,
            'limit' => $limit,
            'sorts' => $sorts,
            'to_index' => 2,
        ]));

        // $this->response->dump();

        // dd($this->response->json());

        $this->assertJsonIndex($founded, $user);
    }

    /** @test */
    public function api_index_functions_users()
    {
        // $this->withoutExceptionHandling();

        $created = 11;
        $limit = 10;
        $founded = 2;
        $role1 = Role::factory()->create(['id' => 1]);
        $role2 = Role::factory()->create(['id' => 2]);
        $user = User::factory($created)->create(['role_id' => $role1->id]);
        $user = User::factory($created + 1)->create(['role_id' => $role2->id]);
        $select = '[{"c":"GROUP-BY","v":"role_id"}, {"c":"COUNT","v":"COUNT(auth_users.id) as \"total\""}]';

        $this->response = $this->json('POST', route('api.users.store', [
            'limit' => $limit,
            'select' => $select,
            'action' => 'countable',
            'to_index' => 2,
        ]));

        // $this->response->dump();

        // dd($this->response->json());

        $this->assertJsonIndex($founded, ['role_id' => 1, 'total' => 11]);
    }
}
