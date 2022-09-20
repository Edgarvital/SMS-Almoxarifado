@extends('templates.principal')

@section('title')
    Unidades
@endsection

@section('content')

    <div class="row" style="border-bottom: #949494 2px solid; padding-bottom: 5px; margin-bottom: 10px">
        <div class="col-md-10">
            <h2>Setor {{$setor->nome}} - Unidades</h2>
        </div>
        <div class="col-md-2">
            <h2 class="text-right" title="Cadastrar Unidade">
                <a type="button" href="{{route('cadastrar.unidade', ['id' => $setor->id])}}" style="color: #23CF5C!important;"><i class="fa-solid fa-circle-plus"></i></a>
            </h2>
        </div>
    </div>

    @if(session()->has('fail'))
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>{{session('fail')}}</strong>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @elseif(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <strong>{{session('success')}}</strong>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <table id="tableUnidadeIndex" class="table table-hover table-responsive-md">
        <thead style="background-color: #151631; color: white; border-radius: 15px">
        <tr>
            <th class="align-middle" scope="col" style="padding-left: 10px">Nome</th>
            <th class="align-middle" scope="col" style="text-align: center; ">CEP</th>
            <th class="align-middle" scope="col" style="text-align: center;">Endereco</th>
            <th class="align-middle" scope="col" style="text-align: center; ">Bairro</th>
            <th class="align-middle" scope="col" style="text-align: center;">Nome do Responsável</th>
            <th class="align-middle" scope="col" style="text-align: center;">Numero do Responsável</th>
            <th class="align-middle" scope="col" style="text-align: center; ">Ações</th>
        </tr>
        </thead>
        <tbody>

        @forelse($unidades as $unidade)
            <tr>
                <td class="text-left" style="text-align: center"> {{ $unidade->nome }} </td>
                <td style="text-align: center"> {{ $unidade->cep }} </td>
                <td style="text-align: center"> {{ $unidade->endereco }}</td>
                <td style="text-align: center"> {{ $unidade->bairro }}</td>
                <td style="text-align: center"> {{ $unidade->usuario->nome }} </td>
                <td style="text-align: center" class="numTel"> {{ $unidade->usuario->numTel }} </td>
                <td style="text-align: center">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="{{route('unidade.recibos', ['id' => $unidade->id])}}" type="button" style="color: #212529" title="Recibos"><i class="fa-solid fa-file-lines" style="font-size: 23px"></i></a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{route('edit.unidade', ['id' => $unidade->id])}}" type="button" style="color: #212529" title="Editar"><i class="fa-solid fa-pen-to-square" style="font-size: 23px"></i></a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{route('remover.unidade', ['id' => $unidade->id])}}" type="button" style="color: #212529" title="Remover"><i class="fa-solid fa-trash-can" style="font-size: 23px"></i></a>
                        </div>
                    </div>
                </td>
            </tr>
        @empty
            <td colspan="9">Sem unidades basicas cadastradas ainda</td>
        @endempty
        </tbody>
    </table>
    <a type="button" href="{{ route('index.setor') }}" class="btn btn-danger m-1" style="width: 150px">Voltar</a>
@endsection

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script type="text/javascript" src="{{asset('js/unidade/index.js')}}"></script>
