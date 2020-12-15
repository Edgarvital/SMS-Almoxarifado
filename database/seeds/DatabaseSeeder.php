<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CargoSeeder::class);
        $this->call(UsuarioSeeder::class);
        $this->call(DepositoSeeder::class);
        $this->call(materialSeeder::class);
        $this->call(EstoqueSeeder::class);
        $this->call(NotificacaoSeeder::class);
    }
}
