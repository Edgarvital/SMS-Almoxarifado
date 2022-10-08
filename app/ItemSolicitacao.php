<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemSolicitacao extends Model
{
    protected $fillable = ['quantidade_solicitada', 'quantidade_aprovada', 'material_id', 'solicitacao_id'];

    public function material()
    {
        return $this->belongsTo('App\Material');
    }

    public function movimento(){
        $this->belongsTo('App\Solicitacao');
    }

    public static $rulesAdd= [
        'quantidade_solicitada' => 'integer|min:1|required',
        'quantidade_aprovada' => 'integer',
        'material_id' => 'bail|numeric|required',
    ];

    public static $rulesEdit= [
        'quantidade_solicitada' => 'integer|min:1|required',
        'quantidade_aprovada' => 'integer',
    ];

    public static $messages = [
        'quantidade_solicitada.required' => 'A quantidade solicitada é um campo obrigatório.',
        'quantidade_solicitada.min' => 'A quantidade solicitada deve ser maior que 0.',
        'quantidade_solicitada.integer' => 'A quantidade solicitada deve ser um número.',
        'quantidade_aprovada.integer' => 'A quantidade aprovada deve ser um número.',
        'material_id.required' => 'A escolha de um material é obrigatória',
        'material_id.numeric' => 'Escolha um material valido.',
    ];

}
