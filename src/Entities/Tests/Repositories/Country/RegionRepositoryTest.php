<?php

namespace Tests\Repositories\Country;

use App\Models\Country\Region;

use Tests\TestCase;
use Tests\ApiTestTrait;

use Illuminate\Support\Facades\App;
use App\Repositories\Country\RegionRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RegionRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    /**
     * @var RegionRepository
     */
    protected $modelRepository;

    public function setUp() : void
    {
        parent::setUp();
        $this->modelRepository = App::make(RegionRepository::class);
    }

    /** @test */
    public function repo_create_region()
    {
        $region = Region::factory()->make()->toArray();

        $createdRegion = $this->modelRepository->create($region);

        $createdRegion = $createdRegion->toArray();
        $this->assertArrayHasKey('id', $createdRegion);
        $this->assertNotNull($createdRegion['id'], 'Created Region must have id specified');
        $this->assertNotNull(Region::find($createdRegion['id']), 'Region with given id must be in DB');
        $this->assertModelData($region, $createdRegion);
    }

    /** @test */
    public function repo_read_region()
    {
        $region = Region::factory()->create();

        $dbRegion = $this->modelRepository->find($region->id);

        $dbRegion = $dbRegion->toArray();
        $this->assertModelData($region->toArray(), $dbRegion);
    }

    /** @test */
    public function repo_update_region()
    {
        $region = Region::factory()->create();
        $fakeRegion = Region::factory()->make()->toArray();

        $updatedRegion = $this->modelRepository->update($region, $fakeRegion);

        $this->assertModelData($fakeRegion, $updatedRegion->toArray());
        $dbRegion = $this->modelRepository->find($region->id);
        $this->assertModelData($fakeRegion, $dbRegion->toArray());
    }

    /** @test */
    public function repo_delete_region()
    {
        $region = Region::factory()->create();

        $resp = $this->modelRepository->delete($region->id);

        $this->assertTrue($resp);
        $this->assertNull(Region::find($region->id), 'Region should not exist in DB');
    }
}
