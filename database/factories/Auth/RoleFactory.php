<?php

namespace Database\Factories\Auth;

use App\Models\Auth\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->word,
            'createdBy' => $this->faker->numberBetween(1, 3),
            'updatedBy' => $this->faker->numberBetween(1, 3),
        ];
    }
}
