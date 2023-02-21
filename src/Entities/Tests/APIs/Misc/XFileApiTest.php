<?php

namespace Tests\APIs\Misc;

use App\Models\Misc\XFile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Juanfv2\BaseCms\Traits\ApiTestTrait;
use Tests\TestCase;

class XFileApiTest extends TestCase
{
    use ApiTestTrait;
    use WithoutMiddleware;
    use DatabaseTransactions;
    // use RefreshDatabase;

    /** @test */
    public function api_index_x_file()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit = 10;
        $offset = 0;
        $models = XFile::factory($created)->create();

        $this->response = $this->json('POST', route('api.x_files.store', ['limit' => $limit, 'offset' => $offset, 'to_index' => 2]));

        // $this->response->dump();
        // dd($this->response->json());

        $this->assertJsonIndex($limit, $models[0]);
    }

    /** @test */
    public function api_create_x_file()
    {
        // $this->withoutExceptionHandling();

        $model = XFile::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('POST', route('api.x_files.store'), $model);

        // $this->response->dump();
        // dd($this->response->json());

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_read_x_file()
    {
        $model = XFile::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('GET', route('api.x_files.show', ['x_file' => $model->id]));

        $this->assertJsonShow($model);
    }

    /** @test */
    public function api_update_x_file()
    {
        $model = XFile::factory()->create();
        $modelEdited = XFile::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('PUT', route('api.x_files.update', ['x_file' => $model->id]), $modelEdited);

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_delete_x_file()
    {
        $model = XFile::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('DELETE', route('api.x_files.destroy', ['x_file' => $model->id]));

        $this->assertApiSuccess();

        $this->response = $this->actingAsAdmin('api')->json('DELETE', route('api.x_files.destroy', ['x_file' => $model->id]));

        $this->response->assertStatus(404);
    }
}
