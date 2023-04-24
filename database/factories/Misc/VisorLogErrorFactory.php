<?php

namespace Database\Factories\Misc;

use Illuminate\Database\Eloquent\Factories\Factory;

class VisorLogErrorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\Misc\VisorLogError::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            'payload' => $this->faker->text($this->faker->numberBetween(5, 6)),
            'queue' => $this->faker->text($this->faker->numberBetween(5, 191)),
            'container_id' => 1,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
        ];
    }
}
