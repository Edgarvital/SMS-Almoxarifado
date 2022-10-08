@extends('templates.principal')

@section('title') Solicitar Material @endsection

@section('content')
    <div style="border-bottom: #949494 2px solid; padding-bottom: 5px; margin-bottom: 10px">
        <h2>SOLICITAR MATERIAL</h2>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <strong>{{session('success')}}</strong>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul>
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div id="error" class="alert alert-danger" role="alert" style="margin-top: 10px; display: none">
        Informe o material e a quantidade!
    </div>

    <div id="remocaoSuccess" class="alert alert-success" role="alert" style="margin-top: 10px; display: none">
        Material removido!
    </div>

    <div id="editSuccess" class="alert alert-success" role="alert" style="margin-top: 10px; display: none">
        Material Editado!
    </div>

    <div style="background-color: #D7D7E6">
        <form method="POST" action="{{ route('add.mat') }}">
            @csrf
            <input type="hidden" name="solicitacao_id" value="{{$solicitacao->id}}">
            <div class="form-row" style="margin-left: 10px">
                <div class="form-group col-md-4">
                    <label for="selectUnidadeBasica" style="color: #151631; font-family: 'Segoe UI'; font-weight: 700">Unidade Básica:</label>
                    <select class="form-control" name="unidade_id" id="selectUnidadeBasica" style="width: 100%" @if(Auth::user()->cargo_id != 1 || !($unidades instanceof \Illuminate\Database\Eloquent\Collection)) disabled @endif>
                        <option></option>
                        @if(Auth::user()->cargo_id == 1)
                            @if($unidades instanceof \Illuminate\Database\Eloquent\Collection)
                                @foreach($unidades as $unidade)
                                    <option value="{{$unidade->id}}">{{ $unidade->nome }} </option>
                                @endforeach
                            @else
                                <option value="{{$unidades->id}}" selected>{{$unidades->nome}}</option>
                            @endif
                        @else
                            <option value="{{$unidades->id}}" selected>{{ $unidades->nome }} </option>
                        @endif
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="selectMaterial" style="color: #151631; font-family: 'Segoe UI'; font-weight: 700">Material</label>
                    <select id="selectMaterial" name="material_id" class="selectMaterial" class="form-control" style="width: 95%;">
                        <option></option>
                        <option value="" disabled>Material[Codigo]</option>
                        @if(\Illuminate\Support\Facades\Auth::user()->cargo_id == 3)
                            @foreach($materiais as $material)
                                <option value="{{$material->id}}">{{$material->nome}}[{{$material->codigo}}]</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label for="quantMaterial" style="color: #151631; font-family: 'Segoe UI'; font-weight: 700">Quantidade</label>
                    <input type="text" min="1" class="form-control" id="quantMaterial" name="quantidade_solicitada" value="{{ old('quantidade_solicitada') }}">
                </div>

                <div class="form-group col-md-2">
                    <button type="submit" id="addTable" style="margin-top: 30px;" class="btn btn-primary">Adicionar</button>
                </div>
            </div>
        </form>
    </div>

    <table id="tableMaterial" class="table table-hover table-responsive-md" style="margin-top: 10px">
        <thead style="background-color: #151631; color: white; border-radius: 15px">
        <tr>
            <th scope="col">Material</th>
            <th scope="col" style="text-align: center">Unidade Básica</th>
            <th scope="col" style="text-align: center">Quantidade</th>
            @if(Auth::user()->cargo_id == 1)
                <th scope="col" style="text-align: center">Estoque</th>
            @endif
            <th scope="col" style="text-align: center">Unidade</th>
            <th scope="col" style="text-align: center">Ações</th>
        </tr>
        </thead>
        <tbody>
        @if(count($itensSolicitacao) > 0)
            @foreach($itensSolicitacao as $item)
                <tr>
                    <td>{{$item->nome}}</td>
                    <td class="text-center">{{$unidades->nome}}</td>
                    <td class="text-center">{{$item->quantidade_solicitada}}</td>
                    @if(Auth::user()->cargo_id == 1)
                        <td class="text-center">{{$item->quantidade}}</td>
                    @endif
                    <td class="text-center">{{$item->unidade}}</td>
                    <td class="text-center">
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                ⋮
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a type="button" class="dropdown-item" data-toggle="modal" data-target="#editar_item_{{$item->id}}">Editar</a>
                                <a type="button" class="dropdown-item" href="{{route('remover.mat', ['item_id' => $item->id])}}">Remover</a>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif
        </tbody>
    </table>

    <form method="POST" id="formSolicitacao" name="formSolicitacao" action="{{ route('add.material') }}">
        @csrf
        <input type="hidden" name="solicitacao_id" value="{{$solicitacao->id}}">
        @if(Auth::user()->cargo_id == 3)
            <div class="form-group col-md-12" class="form-row" style="border-bottom: #cfc5c5 1px solid; padding: 0 0 20px 0;">
                <label for="inputObservacao"><strong>Observações:</strong></label>
                <textarea class="form-control" name="observacao_requerente" id="inputObservacao" cols="30" rows="3">{{ old('observacao') }}</textarea>
            </div>
        @endif

        <Button class="btn btn-secondary" type="button" onclick="location.href = '../' "> Cancelar</Button>
        <button id="solicita" class="btn btn-success" @if(count($itensSolicitacao) == 0) disabled @endif onclick="return setValuesRowInput()">Solicitar</button>
    </form>

    @foreach($itensSolicitacao as $item)
        <div class="modal fade" id="editar_item_{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel" style="color:#151631">Editar Solicitação</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="modalBody">
                            <form method="post" action="{{route('editar.mat')}}">
                                @csrf
                                <input type="hidden" name="item_id" value="{{$item->id}}">
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label for="MaterialEdit" style="color: #151631; font-family: 'Segoe UI'; font-weight: 700;">Material</label>
                                        <input class="form-control" id="materialEdit" type="text" value="{{$item->nome}}" disabled>
                                    </div>

                                    <div class="form-group col-md-2" style="margin-left: 4%">
                                        <label for="quantidade_solicitada" style="color: #151631; font-family: 'Segoe UI'; font-weight: 700">Quantidade</label>
                                        <input type="number" min="1" onkeypress="return onlyNums(event,this);" class="form-control" id="quantidade_solicitada" name="quantidade_solicitada"
                                               value="{{ old('quantidade', $item->quantidade_solicitada) }}">
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" id="updateMaterial" style="margin-top: 30px; margin-left: 10px" class="btn btn-primary">Atualizar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        var cont = 0;
        $('#selectUnidadeBasica').select2({
            placeholder: "Selecione a Unidade Básica.",
            language: {noResults: () => "Nenhum resultado encontrado.",},
        });
    </script>
@endsection

@section('post-script')
    @if(Auth::user()->cargo_id == 1 && !($unidades instanceof \Illuminate\Database\Eloquent\Collection))
        <script type="text/javascript">
            $(function () {
                var unidade_id = $("#selectUnidadeBasica option:selected").val();

                $.get('/get_materiais/' + unidade_id, function (estoques) {
                    $.each(estoques, function (key, value) {
                        $('#selectMaterial').append(`<option value="${value.material_id}" id="Material${value.material_id}">${value.nome}[${value.codigo} - ${value.quantidade}]</option>`);
                        $('#selectMaterialEdit').append(`<option value="${value.material_id}" id="MaterialEdit${value.material_id}">${value.nome}[${value.codigo} - ${value.quantidade}]</option>`);
                        $('#estoquesId').append(`<input type="hidden" id="estoque_${value.material_id}${unidade_id}" value="${value.quantidade}">`);
                    });
                });
            });
        </script>
    @elseif(Auth::user()->cargo_id == 1)
        <script type="text/javascript">

            $('#selectUnidadeBasica').change(function () {
                var unidade_id = $("#selectUnidadeBasica option:selected").val();

                $.get('/get_materiais/' + unidade_id, function (estoques) {
                    $.each(estoques, function (key, value) {
                        $('#selectMaterial').append(`<option value="${value.material_id}" id="Material${value.material_id}">${value.nome}[${value.codigo} - ${value.quantidade}]</option>`);
                        $('#selectMaterialEdit').append(`<option value="${value.material_id}" id="MaterialEdit${value.material_id}">${value.nome}[${value.codigo} - ${value.quantidade}]</option>`);
                        $('#estoquesId').append(`<input type="hidden" id="estoque_${value.material_id}${unidade_id}" value="${value.quantidade}">`);
                    });
                });

            });
        </script>
    @endif
@endsection

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js"></script>
@if(Auth::user()->cargo_id == 1)
    <script type="text/javascript" src="{{asset('js/solicitacoes/solicita_material.js')}}"></script>
@else
    <script type="text/javascript" src="{{asset('js/solicitacoes/solicita_material_requerente.js')}}"></script>
@endif
