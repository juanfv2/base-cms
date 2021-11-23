<?php

namespace Database\Factories\Misc;

use App\Models\Misc\BulkError;
use Illuminate\Database\Eloquent\Factories\Factory;

class BulkErrorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BulkError::class;

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
            'created_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
