@extends('../templates.principal')

@section('title')
    Notas Fiscais
@endsection

@section('content')

    <div class="row" style="border-bottom: #949494 2px solid; padding-bottom: 5px; margin-bottom: 10px">
        <div class="col-md-10">
            <h2 style="margin-bottom: 0px">Notas Fiscais da Ordem de Fornecimento {{$ordem->codigo}}</h2>
        </div>
        <div class="col-md-2">
            <h2 class="text-right" title="Cadastrar Nota Fiscal">
                <a type="button" href="{{route('cadastrar.nota',['id' => $ordem->id])}}" style="color: #23CF5C!important;"><i class="fa-solid fa-circle-plus"></i></a>
            </h2>
        </div>
    </div>

    @if(session()->has('fail'))
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>{{session('fail')}}</strong>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @elseif(session()->has('success') || isset($success))
        <div class="alert alert-success alert-dismissible fade show">
            <strong>@if(isset($success)) {{$success}} @else {{session('success')}} @endif</strong>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <table id="tableNotas" class="table table-hover table-responsive-md">
        <thead class="terciaria-bg" style=" color: white; border-radius: 15px">
        <tr>
            <th class="align-middle" scope="col" style="padding-left: 10px">Número</th>
            <th class="align-middle" scope="col" style="text-align: center">Série</th>
            <th class="align-middle" scope="col" style="text-align: center">Valor</th>
            <th class="align-middle" scope="col" style="text-align: center">Emitente</th>
            <th class="align-middle" scope="col" style="text-align: center">Status</th>
            <th scope="col" style="text-align: center; width: 3%">Ações</th>
        </tr>
        </thead>
        <tbody>

        @forelse($notas as $nota)
            <tr data-toggle="modal" data-target="#notaFiscal{{$nota->id}}">
                <td class="text-left" style="text-align: center"> {{ $nota->numero }} </td>
                <td style="text-align: center"> {{ $nota->serie }} </td>
                <td style="text-align: center"> R$ {{ number_format((float)$nota->valor_nota, 2, ',', '')}}</td>
                <td style="text-align: center"> {{ $nota->emitente->razao_social}} </td>
                <td style="text-align: center">
                    @if($nota->status == "Concluido")
                    <strong class="alert-success">{{$nota->status}}</strong>
                    @else
                        <strong class="alert-danger">{{$nota->status}}</strong>
                    @endif
                </td>

                <td style="text-align: center">
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown" type="button" id="dropdownMenuButton"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" @if($nota->status == "Concluido") disabled @endif>
                            ⋮
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a type="button" class="dropdown-item" onclick="location.href = '{{ route('edit.nota', ['id' => $nota->id]) }}'">Editar Nota Fiscal</a>
                            <a type="button" class="dropdown-item" onclick="location.href = '{{ route('materiais_edit.nota', ['nota' => $nota->id]) }}'">Editar da Materiais</a>

                            <a type="button" class="dropdown-item"
                               onclick="if(confirm('Tem certeza que deseja Remover a nota fiscal {{$nota->codigo}}?')) location.href='{{route('remover.nota', $nota->id)}}'">Remover</a>
                        </div>
                    </div>
                </td>
            </tr>
        @empty
            <td colspan="2">Sem notas fiscais cadastradas ainda</td>
        @endempty
        </tbody>
    </table>
    <a type="button" href="{{ route('index.ordemFornecimento') }}" class="btn btn-danger m-1" style="width: 150px">Voltar</a>

    @foreach($notas as $nota)
        <div class="modal fade bd-example-modal-lg" id="notaFiscal{{$nota->id}}" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="TituloModalLongoExemplo">Nota Fiscal {{$nota->codigo}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding-top: 0px">
                        <div class="form-group row pr-3 pl-3">
                            @php $materialNota = \App\MaterialNotas::where('nota_fiscal_id', $nota->id)->get() @endphp
                            @if(isset($materialNota))
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th scope="col">Material</th>
                                        <th scope="col">Quantidade</th>
                                        <th scope="col">Valor do Item</th>
                                        <th scope="col">Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    @foreach($materialNota as $material)
                                        <tr>
                                            <td>
                                                {{\App\Material::find($material->material_id)->nome}}
                                            </td>
                                            <td>{{$material->quantidade}}</td>
                                            <td>R$ {{$material->valor}} </td>
                                            <td>@if($material->status == false)
                                                    <strong class="alert-danger">
                                                        Pendente
                                                    </strong>
                                                @else
                                                    <strong class="alert-success">Recebido</strong>
                                                @endif</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script type="text/javascript" src="{{asset('js/nota/index.js')}}"></script>
