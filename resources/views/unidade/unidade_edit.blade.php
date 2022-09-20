@extends('templates.principal')

@section('title') Cadastrar Unidade @endsection

@section('content')
    <div style="border-bottom: #949494 2px solid; padding: 5px; margin-bottom: 10px">
        <h2>EDITAR UNIDADE</h2>
    </div>

    <form method="POST" action="{{ route('alterar.unidade') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="unidade_id" value="{{$unidade->id}}">

        <div class="form-group">
            <h2 class="h4"> Informações da Unidade</h2>
        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="nome">Nome<span style="color: red">*</span></label>
                <input class="form-control" type="text" id="nome" name="nome" placeholder="Digite o nome da unidade" value="{{$unidade->nome}}" required>
            </div>
            <div class="col-md-6">
                <label for="nome">Setor<span style="color: red">*</span></label>
                <select class="form-control" name="setor" id="setor" readonly style="pointer-events: none">
                    <option value="{{$unidade->setor->id}}" selected>{{$unidade->setor->nome}}</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <h2 class="h4"> Endereço da Unidade</h2>
        </div>

        <div class="form-group row">
            <div class="col-md-4">
                <label for="cep">CEP<span style="color: red">*</span></label>
                <input class="form-control" type="text" id="cep" name="cep" onblur="pesquisacep(this.value)" placeholder="Digite o cep da unidade" value="{{$unidade->cep}}" required>
            </div>

            <div class="col-md-4">
                <label for="endereco">Endereco<span style="color: red">*</span></label>
                <input class="form-control" type="text" id="endereco" name="endereco" placeholder="Digite o endereco da unidade" value="{{$unidade->endereco}}" required>
            </div>

            <div class="col-md-4">
                <label for="bairro">Bairro<span style="color: red">*</span></label>
                <input class="form-control" type="text" id="bairro" name="bairro" placeholder="Digite o bairro da unidade" value="{{$unidade->bairro}}" required>
            </div>
        </div>

        <div class="form-group">
            <h2 class="h4"> Dados do Responsável/Login</h2>
        </div>

        <div class="form-group row">
            <div class="col-md-4">
                <a class="btn btn-primary w-100"
                   href="{{ route('usuario.edit', ['usuario' => $unidade->usuario_id]) }}">Editar Usuário Responsável</a>
            </div>
        </div>


        <div class="form-group row">
            <div class="col-md-4">
                <a type="button" class="btn btn-secondary" href="{{route('index.unidade', ['id' => $unidade->setor->id])}}">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-success">Alterar</button>
            </div>
        </div>
    </form>
@endsection

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script type="text/javascript" src="{{asset('js/material/CheckFields.js')}}"></script>
<script type="text/javascript" src="{{asset('js/unidade/busca.js')}}"></script>

