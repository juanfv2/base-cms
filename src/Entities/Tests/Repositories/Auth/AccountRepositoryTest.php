<?php

namespace Tests\Repositories\Auth;

use App\Models\Auth\Account;

use Tests\TestCase;
use Juanfv2\BaseCms\Traits\ApiTestTrait;

use Illuminate\Support\Facades\App;
use App\Repositories\Auth\AccountRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AccountRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    /**
     * @var AccountRepository
     */
    protected $modelRepository;

    public function setUp() : void
    {
        parent::setUp();
        $this->modelRepository = App::make(AccountRepository::class);
    }

    /** @test */
    public function repo_create_account()
    {
        $account = Account::factory()->make()->toArray();

        $createdAccount = $this->modelRepository->create($account);

        $createdAccount = $createdAccount->toArray();
        $this->assertArrayHasKey('id', $createdAccount);
        $this->assertNotNull($createdAccount['id'], 'Created Account must have id specified');
        $this->assertNotNull(Account::find($createdAccount['id']), 'Account with given id must be in DB');
        $this->assertModelData($account, $createdAccount);
    }

    /** @test */
    public function repo_read_account()
    {
        $account = Account::factory()->create();

        $dbAccount = $this->modelRepository->find($account->id);

        $dbAccount = $dbAccount->toArray();
        $this->assertModelData($account->toArray(), $dbAccount);
    }

    /** @test */
    public function repo_update_account()
    {
        $account = Account::factory()->create();
        $fakeAccount = Account::factory()->make()->toArray();

        $updatedAccount = $this->modelRepository->update($account, $fakeAccount);

        $this->assertModelData($fakeAccount, $updatedAccount->toArray());
        $dbAccount = $this->modelRepository->find($account->id);
        $this->assertModelData($fakeAccount, $dbAccount->toArray());
    }

    /** @test */
    public function repo_delete_account()
    {
        $account = Account::factory()->create();

        $resp = $this->modelRepository->delete($account->id);

        $this->assertTrue($resp);
        $this->assertNull(Account::find($account->id), 'Account should not exist in DB');
    }
}
