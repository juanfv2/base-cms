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
            'payload' => $this->faker->text,
            'queue' => $this->faker->word,
            'container_id' => 1,
        ];
    }
}
