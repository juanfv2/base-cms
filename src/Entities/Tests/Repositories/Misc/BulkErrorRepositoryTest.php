<?php

namespace Tests\Repositories\Misc;

use App\Models\Misc\BulkError;

use Tests\TestCase;
use Juanfv2\BaseCms\Traits\ApiTestTrait;

use Illuminate\Support\Facades\App;
use App\Repositories\Misc\BulkErrorRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BulkErrorRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    /**
     * @var BulkErrorRepository
     */
    protected $modelRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->modelRepository = App::make(BulkErrorRepository::class);
    }

    /** @test */
    public function repo_create_bulk_error()
    {
        $bulkError = BulkError::factory()->make()->toArray();

        $createdBulkError = $this->modelRepository->create($bulkError);

        $createdBulkError = $createdBulkError->toArray();
        $this->assertArrayHasKey('id', $createdBulkError);
        $this->assertNotNull($createdBulkError['id'], 'Created BulkError must have id specified');
        $this->assertNotNull(BulkError::find($createdBulkError['id']), 'BulkError with given id must be in DB');
        $this->assertModelData($bulkError, $createdBulkError);
    }

    /** @test */
    public function repo_read_bulk_error()
    {
        $bulkError = BulkError::factory()->create();

        $dbBulkError = $this->modelRepository->find($bulkError->id);

        $dbBulkError = $dbBulkError->toArray();
        $this->assertModelData($bulkError->toArray(), $dbBulkError);
    }

    /** @test */
    public function repo_update_bulk_error()
    {
        $bulkError = BulkError::factory()->create();
        $fakeBulkError = BulkError::factory()->make()->toArray();

        $updatedBulkError = $this->modelRepository->update($bulkError, $fakeBulkError);

        $this->assertModelData($fakeBulkError, $updatedBulkError->toArray());
        $dbBulkError = $this->modelRepository->find($bulkError->id);
        $this->assertModelData($fakeBulkError, $dbBulkError->toArray());
    }

    /** @test */
    public function repo_delete_bulk_error()
    {
        $bulkError = BulkError::factory()->create();

        $resp = $this->modelRepository->delete($bulkError->id);

        $this->assertTrue($resp);
        $this->assertNull(BulkError::find($bulkError->id), 'BulkError should not exist in DB');
    }
}
