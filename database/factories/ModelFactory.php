<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Task;
use App\Models\User;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'address' => $faker->address,
        'role' => User::ROLE_USER,
        'status' => User::STATUS_PENDING,
        'password' => app('hash')->make('qweqweqwe'),
    ];
});

$factory->define(Task::class, function (Faker $faker) {
    return [
        'title' => $faker->title,
        'description' => $faker->text,
        'category' => $faker->word,
    ];
});

$factory->state(App\Models\User::class, 'approved', [
    'status' => User::STATUS_APPROVED,
    'credits' => 10
]);

$factory->state(App\Models\User::class, 'admin', [
    'status' => User::STATUS_APPROVED,
    'role' => User::ROLE_ADMIN,
]);
