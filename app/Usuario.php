<?php

namespace App;

use App\Notifications\ResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;
    use SoftDeletes;

    public static $rules = [
        'nome' => 'required|string|min:5|max:100',
        'email' => 'required|email|min:5|max:100|unique:usuarios',
        'cpf' => 'required|numeric|min:0|digits_between:10,11|unique:usuarios',
        'rg' => 'required|numeric|min:0|digits_between:7,11|unique:usuarios',
        'data_nascimento' => 'required|date',
        'matricula' => 'required|integer|min:1|unique:usuarios',
        'numTel' => 'required|integer|digits:11',
        'password' => 'required|string|min:8|confirmed',
    ];

    public static $messages = [
        'nome.required' => 'O nome é um campo obrigatório.',
        'nome.min' => 'O nome deve ter no mínimo 5 caracteres.',
        'nome.max' => 'O nome deve ter no máximo 100 caracteres.',
        'email.required' => 'O e-mail é um campo obrigatório.',
        'email.min' => 'O e-mail deve ter no mínimo 5 caracteres.',
        'email.max' => 'O e-mail deve ter no máximo 100 caracteres.',
        'email.unique' => 'Este e-mail já está sendo usado.',
        'numTel.required' => 'O número de celular é um campo obrigatório.',
        'numTel.integer' => 'O número de celular deve conter apenas números.',
        'numTel.min' => 'O número de celular não pode ser um número negativo.',
        'numTel.digits' => 'O número de celular deve ter 11 dígitos.',
        'cpf.required' => 'O CPF é um campo obrigatório.',
        'cpf.numeric' => 'O CPF deve conter apenas números.',
        'cpf.min' => 'O CPF não pode ser um número negativo.',
        'cpf.digits_between' => 'O CPF deve ter entre 10 e 11 dígitos.',
        'cpf.unique' => 'O CPF já está cadastrado',
        'rg.required' => 'O RG é um campo obrigatório.',
        'rg.numeric' => 'O RG deve conter apenas números.',
        'rg.min' => 'O RG não pode ser um número negativo.',
        'rg.digits_between' => 'O RG deve ter entre 7 à 11 dígitos.',
        'rg.unique' => 'O RG já está cadastrado',
        'data_nascimento.required' => 'A data de nascimento é um campo obrigatório.',
        'data_nascimento.date' => 'A data de nascimento deve ser no formato de data.',
        'matricula.required' => 'A matricula é um campo obrigatório.',
        'matricula.min' => 'A matricula deve conter pelo menos 1 dígito.',
        'matricula.integer' => 'A matricula deve ser um número.',
        'matricula.unique' => 'A matrícula já está cadastrada',
        'password.required' => 'A senha é um campo obrigatório.',
        'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
        'password.confirmed' => 'As senhas devem ser iguais.',
    ];

    protected $table = 'usuarios';

    protected $fillable = ['nome', 'cpf', 'numTel', 'rg', 'data_nascimento', 'matricula', 'cargo_id', 'email', 'senha', 'setor'];

    protected $hidden = [
        'senha', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getCargo($cargo_id)
    {
        return Cargo::find($cargo_id);
    }

    public function notificacoes()
    {
        return $this->hasMany(Notificacao::class, 'usuario_id')->orderBy('created_at', 'desc')->take(5);
    }

    public function getAuthPassword()
    {
        return $this->senha;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function unidade(){
        $this->hasOne('App\Unidade');
    }
}
