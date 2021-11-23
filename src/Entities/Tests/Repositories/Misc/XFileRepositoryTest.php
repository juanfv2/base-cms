<?php

namespace Tests\Repositories\Misc;

use App\Models\Misc\XFile;

use Tests\TestCase;
use Tests\ApiTestTrait;

use Illuminate\Support\Facades\App;
use App\Repositories\Misc\XFileRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class XFileRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    /**
     * @var XFileRepository
     */
    protected $modelRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->modelRepository = App::make(XFileRepository::class);
    }

    /** @test */
    public function repo_create_x_file()
    {
        $xFile = XFile::factory()->make()->toArray();

        $createdXFile = $this->modelRepository->create($xFile);

        $createdXFile = $createdXFile->toArray();
        $this->assertArrayHasKey('id', $createdXFile);
        $this->assertNotNull($createdXFile['id'], 'Created XFile must have id specified');
        $this->assertNotNull(XFile::find($createdXFile['id']), 'XFile with given id must be in DB');
        $this->assertModelData($xFile, $createdXFile);
    }

    /** @test */
    public function repo_read_x_file()
    {
        $xFile = XFile::factory()->create();

        $dbXFile = $this->modelRepository->find($xFile->id);

        $dbXFile = $dbXFile->toArray();
        $this->assertModelData($xFile->toArray(), $dbXFile);
    }

    /** @test */
    public function repo_update_x_file()
    {
        $xFile = XFile::factory()->create();
        $fakeXFile = XFile::factory()->make()->toArray();

        $updatedXFile = $this->modelRepository->update($xFile, $fakeXFile);

        $this->assertModelData($fakeXFile, $updatedXFile->toArray());
        $dbXFile = $this->modelRepository->find($xFile->id);
        $this->assertModelData($fakeXFile, $dbXFile->toArray());
    }

    /** @test */
    public function repo_delete_x_file()
    {
        $xFile = XFile::factory()->create();

        $resp = $this->modelRepository->delete($xFile->id);

        $this->assertTrue($resp);
        $this->assertNull(XFile::find($xFile->id), 'XFile should not exist in DB');
    }
}
