<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Auth\Role;
use Faker\Generator as Faker;

$factory->define(Role::class, function (Faker $faker) {

    return [
        'name' => $faker->word,
        'description' => $faker->word,
        // 'createdBy' => $faker->word,
        // 'updatedBy' => $faker->word,
        // 'created_at' => $faker->date('Y-m-d H:i:s'),
        // 'updated_at' => $faker->date('Y-m-d H:i:s'),
        // 'deleted_at' => $faker->date('Y-m-d H:i:s')
    ];
});
