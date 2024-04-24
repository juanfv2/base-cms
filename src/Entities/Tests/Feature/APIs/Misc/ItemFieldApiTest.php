<?php

namespace Tests\Feature\APIs\Misc;

use App\Models\Misc\ItemField;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Juanfv2\BaseCms\Traits\ApiTestTrait;
use Tests\TestCase;

class ItemFieldApiTest extends TestCase
{
    use ApiTestTrait;
    use DatabaseTransactions;
    use WithoutMiddleware;
    // use RefreshDatabase;

    public function test_api_index_item_fields()
    {
        $this->withoutExceptionHandling();

        $limit = -1;
        $offset = 0;

        $json = \Illuminate\Support\Facades\File::get('database/data/auth/z_base_cms_fields.json');
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

        $item = new ItemField();
        $item->truncate();
        $item->insert($data);

        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        $this->response = $this->json('POST', route('api.item-fields.store', ['limit' => $limit, 'offset' => $offset, 'to_index' => 2]));

        // $this->response->dd();
        $items = $this->response->json();

        $this->assertCount(75, $items['data']['content']);
    }
}
