<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\Models\Auth\Permission;

$factory->define(Permission::class, function (Faker $faker) {

    return [
        'icon' => $faker->word,
        'name' => $faker->word,
        'urlBackEnd' => $faker->word,
        'urlFrontEnd' => $faker->word,
        'isSection' => $faker->boolean(),
        'isVisible' => $faker->boolean(),
        'orderInMenu' => $faker->randomDigitNotNull,
        'permission_id' => $faker->numberBetween(0, 10),
        // 'createdBy' => $faker->word,
        // 'updatedBy' => $faker->word,
        // 'created_at' => $faker->date('Y-m-d H:i:s'),
        // 'updated_at' => $faker->date('Y-m-d H:i:s'),
        // 'deleted_at' => $faker->date('Y-m-d H:i:s')
    ];
});
