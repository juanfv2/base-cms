<?php

namespace Database\Factories\Auth;

use App\Models\Auth\XFile;
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
            'entity_id' => $this->faker->numberBetween(1, 1000),
            'field' => $this->faker->word,
            'name' => $this->faker->word,
            'nameOriginal' => $this->faker->word,
            'publicPath' => $this->faker->word,
            'extension' => $this->faker->countryCode,
            'data' => $this->faker->text,
        ];
    }
}
