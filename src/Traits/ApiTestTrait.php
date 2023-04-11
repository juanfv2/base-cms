<?php

namespace Juanfv2\BaseCms\Traits;

use App\Models\Auth\Account;
use App\Models\Auth\Person;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\AssertableJson;

trait ApiTestTrait
{
    private $response;

    public function assertApiSuccess()
    {
        $this->response->assertStatus(200);
        $this->response->assertJson(['success' => true]);
    }

    public function assertJsonIndex($limit, $firstItem)
    {
        $this->assertApiSuccess();
        $this->response->assertJson(
            fn (AssertableJson $json) => $json
                ->has('message')
                ->has('success')
                ->has('data.content', $limit)
                ->has('data.content.0', fn ($json) => $this->jsonValidate($json, $firstItem))
            //  ->has( 'data.content.0', fn ($json) => $json->where('id', $area->id) ->where('name', $area->name) ->missing('created_by') ->etc())

        );
    }

    public function assertJsonShow($item)
    {
        $this->assertApiSuccess();
        $this->response->assertJson(
            fn (AssertableJson $json) => $json
                ->has('message')
                ->has('success')
                ->has('data', fn ($json) => $this->jsonValidate($json, $item))
            //  ->has( 'data.content.0', fn ($json) => $json->where('id', $area->id) ->where('name', $area->name) ->missing('created_by') ->etc())

        );
    }

    public function assertJsonErrors($item)
    {
        $this->response->assertJson(
            fn (AssertableJson $json) => $json
                ->has('message')
                ->has('errors', fn ($json) => $this->jsonValidateErrors($json, $item))

        );
    }

    public function assertJsonModifications($_type = 'integer')
    {
        $this->assertApiSuccess();

        $this->response->assertJson(
            fn (AssertableJson $json) => $json
                ->has('message')
                ->has('success')
                ->whereType('data.id', $_type)
                ->etc()

        );
    }

    public function jsonValidate($json, $currentModel)
    {
        $cModel = [];
        $hidden = [];
        $attributes = [];

        if ($currentModel instanceof Model) {
            $hidden = $currentModel->getHidden();
            $attributes = array_diff(array_keys($currentModel->getAttributes()), $hidden);
            $cModel = $currentModel->toArray();
        } elseif (is_array($currentModel)) {
            $hidden = [];
            $attributes = array_diff(array_keys($currentModel), $hidden);
            $cModel = $currentModel;
        }

        foreach ($attributes as $key) {
            // logger(__FILE__ . ':' . __LINE__ . ' $currentModel->$key ', [$key, $cModel[$key]]);
            $json->where($key, $cModel[$key]);
        }

        foreach ($hidden as $key) {
            $json->missing($key);
        }

        return $json->etc();
    }

    public function jsonValidateErrors($json, $errors)
    {
        foreach ($errors as $key => $values) {
            // foreach ($values as $value) {
            $json->where($key, $values);
            // }
        }

        return $json->etc();
    }

    /**
     * Return an admin user
     *
     * @return User $admin
     */
    protected function admin($overrides = [])
    {
        $person = Person::factory()->create();
        $user = User::find($person->user_id);

        $user->roles()->attach(
            Role::factory()->admin()->create()
        );

        return $user;
    }

    /**
     * Return an user
     *
     * @return User
     */
    protected function account($overrides = [])
    {
        $account = Account::factory()->create();
        $user = User::find($account->user_id);

        $user->roles()->attach(
            Role::factory()->account()->create()
        );

        return $user;
    }

    /**
     * Return an user
     *
     * @return User
     */
    protected function user($overrides = [])
    {
        return User::factory()->create($overrides);
    }

    /**
     * Acting as an admin
     */
    protected function actingAsAdmin($api = null)
    {
        $this->actingAs($this->admin(), $api);

        return $this;
    }

    /**
     * Acting as an user
     */
    protected function actingAsAccount($api = null)
    {
        $this->actingAs($this->account(), $api);

        return $this;
    }
}
