<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Unidade extends Model
{
    public function solicitacao()
    {
        return $this->belongsTo('App\Unidade', 'unidade_id');
    }

    public function setor(){
        return $this->belongsTo('App\Setor');
    }

    public function usuario()
    {
        return $this->belongsTo('App\Usuario', 'usuario_id');
    }

}
