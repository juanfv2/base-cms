<?php

namespace Database\Factories\Auth;

use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\Auth\Permission::class;

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
            'isSection' => $this->faker->boolean(),
            'isVisible' => $this->faker->boolean(),
            'orderInMenu' => $this->faker->randomDigitNotNull,
            'permission_id' => $this->faker->numberBetween(0, 10),
        ];
    }
}
