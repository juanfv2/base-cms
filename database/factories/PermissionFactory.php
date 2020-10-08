<?php

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Permission::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'icon' => $this->faker->word,
        'name' => $this->faker->word,
        'urlBackEnd' => $this->faker->word,
        'urlFrontEnd' => $this->faker->word,
        'isSection' => $this->faker->word,
        'isVisible' => $this->faker->word,
        'orderInMenu' => $this->faker->randomDigitNotNull,
        'permission_id' => $this->faker->word,
        'createdBy' => $this->faker->word,
        'updatedBy' => $this->faker->word,
        'created_at' => $this->faker->date('Y-m-d H:i:s'),
        'updated_at' => $this->faker->date('Y-m-d H:i:s'),
        'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
