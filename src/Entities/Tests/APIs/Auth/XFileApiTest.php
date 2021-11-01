<?php

namespace Tests\APIs\Auth;

use App\Models\Auth\XFile;

use Tests\TestCase;
use Tests\ApiTestTrait;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class XFileApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions; // , RefreshDatabase

    /** @test */
    public function api_create_x_file()
    {
        // $this->withoutExceptionHandling();

        $xFile = XFile::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('POST', route('api.x_files.store'), $xFile);

        // $this->response->dump();

        $this->assertApiModifications($xFile);
    }

    /** @test */
    public function api_read_x_file()
    {
        $xFile = XFile::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('GET', route('api.x_files.show', ['x_file' => $xFile->id]));

        $this->assertApiResponse($xFile->toArray());
    }

    /** @test */
    public function api_update_x_file()
    {
        $xFile = XFile::factory()->create();
        $editedXFile = XFile::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('PUT', route('api.x_files.update', ['x_file' => $xFile->id]), $editedXFile);

        $this->assertApiModifications($editedXFile);
    }

    /** @test */
    public function api_delete_x_file()
    {
        $xFile = XFile::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('DELETE', route('api.x_files.destroy', ['x_file' => $xFile->id]));

        $this->assertApiSuccess();

        $this->response = $this->actingAsAdmin('api')->json('GET', route('api.x_files.show', ['x_file' => $xFile->id]));

        $this->response->assertStatus(404);
    }
}
