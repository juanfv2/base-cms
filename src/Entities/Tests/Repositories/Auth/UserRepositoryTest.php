<?php

namespace Tests\Repositories\Auth;

use App\Models\Auth\User;

use Tests\TestCase;
use Tests\ApiTestTrait;

use Illuminate\Support\Facades\App;
use App\Repositories\Auth\UserRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    /**
     * @var UserRepository
     */
    protected $modelRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->modelRepository = App::make(UserRepository::class);
    }

    /** @test */
    public function repo_create_user()
    {
        $user = User::factory()->make()->toArray();
        $user['password'] = '--';

        $createdUser = $this->modelRepository->create($user);

        $createdUser = $createdUser->toArray();
        $createdUser['password'] = '--';
        $this->assertArrayHasKey('id', $createdUser);
        $this->assertNotNull($createdUser['id'], 'Created User must have id specified');
        $this->assertNotNull(User::find($createdUser['id']), 'User with given id must be in DB');
        $this->assertModelData($user, $createdUser);
    }

    /** @test */
    public function repo_read_user()
    {
        $user = User::factory()->create();

        $dbUser = $this->modelRepository->find($user->id);

        $dbUser = $dbUser->toArray();
        $this->assertModelData($user->toArray(), $dbUser);
    }

    /** @test */
    public function repo_update_user()
    {
        $user = User::factory()->create();
        $fakeUser = User::factory()->make()->toArray();

        $updatedUser = $this->modelRepository->update($user, $fakeUser);

        $this->assertModelData($fakeUser, $updatedUser->toArray());
        $dbUser = $this->modelRepository->find($user->id);
        $this->assertModelData($fakeUser, $dbUser->toArray());
    }

    /** @test */
    public function repo_delete_user()
    {
        $user = User::factory()->create();

        $resp = $this->modelRepository->delete($user->id);

        $this->assertTrue($resp);
        $this->assertNull(User::find($user->id), 'User should not exist in DB');
    }
}
