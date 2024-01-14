<?php

namespace Database\Factories\Auth;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\Auth\Person::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user = User::factory()->create();

        return [
            'id' => $user->id,
            'user_id' => $user->id,
            'first_name' => $this->faker->first_name,
            'last_name' => $this->faker->last_name,
            'cell_phone' => $this->faker->numerify('0##########'),
            'birth_date' => $this->faker->date('Y-m-d'),
            'address' => $this->faker->text($this->faker->numberBetween(5, 96)),
            'neighborhood' => $this->faker->text($this->faker->numberBetween(5, 96)),
            // 'created_by' => $this->faker->word,
            // 'updated_by' => $this->faker->word,
            // 'created_at' => $this->faker->date('Y-m-d H:i:s'),
            // 'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            // 'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
