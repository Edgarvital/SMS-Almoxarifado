<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call(CargoSeeder::class);
        $this->call(UsuarioSeeder::class);
        $this->call(DepositoSeeder::class);
        //$this->call(SetorSeeder::class);
        //$this->call(UnidadeSeeder::class);
        //$this->call(MaterialSeeder::class);
        //$this->call(EstoqueSeeder::class);
        //$this->call(OrdemFornecimentoSeeder::class);
        //$this->call(EmitenteSeeder::class);
        //$this->call(NotaFiscalSeeder::class);
        //$this->call(MaterialNotasSeeder::class);
        //$this->call(NotificacaoSeeder::class);
    }
}
