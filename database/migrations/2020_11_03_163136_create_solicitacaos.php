<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSolicitacaos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solicitacaos', function (Blueprint $table) {
            $table->id();
            $table->string('observacao_requerente')->nullable();
            $table->string('observacao_admin')->nullable();
            $table->unsignedInteger('unidade_id')->index()->nullable();
            $table->foreign('unidade_id')->references('id')->on('unidades');

            $table->unsignedInteger('admin_id')->index()->nullable();
            $table->foreign('admin_id')->references('id')->on('usuarios');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('solicitacaos');
    }
}
