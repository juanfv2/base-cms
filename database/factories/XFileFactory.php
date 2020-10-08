<?php

namespace Database\Factories;

use App\Models\XFile;
use Illuminate\Database\Eloquent\Factories\Factory;

class XFileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = XFile::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'entity' => $this->faker->word,
        'entity_id' => $this->faker->randomDigitNotNull,
        'field' => $this->faker->word,
        'name' => $this->faker->word,
        'nameOriginal' => $this->faker->word,
        'extension' => $this->faker->word,
        'data' => $this->faker->text,
        'created_at' => $this->faker->date('Y-m-d H:i:s'),
        'updated_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
