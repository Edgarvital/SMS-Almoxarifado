<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Unidade;
use Faker\Generator as Faker;

$factory->define(Unidade::class, function (Faker $faker) {
    return [
        'nome' => 'Unidade ' . $faker->company,
        'cep' => $faker->postcode,
        'endereco' => $faker->address,
        'bairro' => $faker->country,
        'setor_id' => 1,
        'usuario_id' => 3
    ];
});
