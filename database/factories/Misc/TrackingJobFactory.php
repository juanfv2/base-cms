<?php

namespace Database\Factories\Misc;

use Illuminate\Database\Eloquent\Factories\Factory;

class TrackingJobFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\Misc\TrackingJob::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => 1,
            'queue' => $this->faker->word,
            'status' => $this->faker->randomDigitNotNull,
            'link' => $this->faker->word,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
        ];
    }
}
