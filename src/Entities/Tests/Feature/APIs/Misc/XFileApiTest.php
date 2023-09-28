<?php

namespace Tests\Feature\APIs\Misc;

use App\Models\Misc\XFile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Juanfv2\BaseCms\Traits\ApiTestTrait;
use Tests\TestCase;

class XFileApiTest extends TestCase
{
    use ApiTestTrait;
    use DatabaseTransactions;
    use WithoutMiddleware;
    // use RefreshDatabase;

    /** @test */
    public function api_index_x_files()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit = 10;
        $offset = 0;
        $models = XFile::factory($created)->create();

        $this->response = $this->json('POST', route('api.x-files.store', ['limit' => $limit, 'offset' => $offset, 'to_index' => 2]));

        // $this->response->dd();

        $this->assertJsonIndex($limit, $models[0]);
    }

    /** @test */
    public function api_create_x_file()
    {
        // $this->withoutExceptionHandling();

        $model = XFile::factory()->make()->toArray();

        $this->response = $this->json('POST', route('api.x-files.store'), $model);

        // $this->response->dd();

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_read_x_file()
    {
        $model = XFile::factory()->create();

        $this->response = $this->json('GET', route('api.x-files.show', ['x_file' => $model->id]));

        $this->assertJsonShow($model);
    }

    /** @test */
    public function api_update_x_file()
    {
        $model = XFile::factory()->create();
        $modelEdited = XFile::factory()->make()->toArray();

        $this->response = $this->json('PUT', route('api.x-files.update', ['x_file' => $model->id]), $modelEdited);

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_delete_x_file()
    {
        $model = XFile::factory()->create();

        $this->response = $this->json('DELETE', route('api.x-files.destroy', ['x_file' => $model->id]));

        $this->assertApiSuccess();

        $this->response = $this->json('DELETE', route('api.x-files.destroy', ['x_file' => $model->id]));

        $this->response->assertStatus(404);
    }
}
