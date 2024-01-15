<?php

namespace Database\Factories\Misc;

use App\Models\Misc\ItemField;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFieldFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ItemField::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            'alias' => $this->faker->text($this->faker->numberBetween(5, 191)),
            'name' => $this->faker->text($this->faker->numberBetween(5, 191)),
            'label' => $this->faker->text($this->faker->numberBetween(5, 191)),
            'field' => $this->faker->text($this->faker->numberBetween(5, 191)),
            'type' => $this->faker->text($this->faker->numberBetween(5, 191)),
            'allowSearch' => $this->faker->boolean,
            'allowExport' => $this->faker->boolean,
            'allowImport' => $this->faker->boolean,
            'allowInList' => $this->faker->boolean,
            'sorting' => $this->faker->boolean,
            'fixed' => $this->faker->boolean,
            'index' => $this->faker->randomNumber(),
            'table' => $this->faker->text($this->faker->numberBetween(5, 191)),
            'model' => $this->faker->text($this->faker->numberBetween(5, 191)),
            'extra' => $this->faker->text($this->faker->numberBetween(5, 65535)),
            'allowNull' => $this->faker->text($this->faker->numberBetween(5, 191)),
            'key' => $this->faker->text($this->faker->numberBetween(5, 191)),
            'defaultValue' => $this->faker->text($this->faker->numberBetween(5, 191)),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
        ];
    }
}
