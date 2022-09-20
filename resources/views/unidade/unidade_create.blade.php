@extends('templates.principal')

@section('title')
    Cadastrar Unidade
@endsection

@section('content')
    <div style="border-bottom: #949494 2px solid; padding: 5px; margin-bottom: 10px">
        <h2>CADASTRAR UNIDADE</h2>
    </div>

    <form method="POST" action="{{ route('criar.unidade') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <h2 class="h4"> Informações da Unidade</h2>
        </div>

        <div class="form-group row">
            <div class="col-md-6">
                <label for="nome">Nome da Unidade<span style="color: red">*</span></label>
                <input class="form-control" type="text" id="nome" name="nome" placeholder="Digite o nome da unidade" required>
            </div>

            <div class="col-md-6">
                <label for="nome">Setor<span style="color: red">*</span></label>
                <select class="form-control" name="setor" id="setor" readonly style="pointer-events: none">
                    <option value="{{$setor->id}}" selected>{{$setor->nome}}</option>
                </select>
            </div>

        </div>

        <div class="form-group">
            <h2 class="h4"> Endereço da Unidade</h2>
        </div>

        <div class="form-group row">
            <div class="col-md-4">
                <label for="cep">CEP<span style="color: red">*</span></label>
                <input class="form-control" type="text" id="cep" name="cep" onblur="pesquisacep(this.value)" placeholder="Digite o cep da unidade" required>
            </div>

            <div class="col-md-4">
                <label for="endereco">Endereco<span style="color: red">*</span></label>
                <input class="form-control" type="text" id="endereco" name="endereco" placeholder="Digite o endereco da unidade" required>
            </div>

            <div class="col-md-4">
                <label for="bairro">Bairro<span style="color: red">*</span></label>
                <input class="form-control" type="text" id="bairro" name="bairro" placeholder="Digite o bairro da unidade" required>
            </div>
        </div>

        <div class="form-group">
            <h2 class="h4"> Dados do Responsável/Login</h2>
        </div>

        <div class="form-group row">
            <div class="col-md-4">
                <label for="email"> E-mail </label>
                <input class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                       autocomplete="email" autofocus type="email" name="email" id="email"
                       placeHolder="exemplodeemail@upe.br">
                @error('email')
                <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="numTel">{{ __('Telefone') }}</label>
                <input id="numTel" type="text" min="0" class="form-control @error('numTel') is-invalid @enderror"
                       name="numTel" value="{{ old('numTel') }}" required autocomplete="numTel"
                       placeholder="(00)00000-0000">

                @error('numTel')
                <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="cpf"> CPF </label>
                <input type="text" name="cpf" id="cpf" class="form-control @error('cpf') is-invalid @enderror"
                       value="{{ old('cpf') }}" autocomplete="cpf" autofocus placeHolder="000.000.000-00">
                @error('cpf')
                <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                @enderror
            </div>


            <input hidden class="form-control @error('senha') is-invalid @enderror" autofocus
                   autocomplete="new-password"
                   type="password" name="password" id="password" placeHolder="" value="almoxarifado123">
            <input hidden class="form-control @error('confirmar_senha') is-invalid @enderror"
                   autocomplete="new-password"
                   autofocus type="password" name="password_confirmation" id="password_confirmation" placeHolder=""
                   value="almoxarifado123">
        </div>

        <div class="form-group row">
            <div class="col-md-4">
                <Button class="btn btn-secondary" type="button"
                        onclick="if(confirm('Tem certeza que deseja Cancelar o cadastro da Unidade?')) location.href = '../' ">
                    Cancelar
                </Button>
                <button type="submit" class="btn btn-success">Cadastrar</button>
            </div>
        </div>
    </form>
@endsection

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script type="text/javascript" src="{{asset('js/unidade/CheckFields.js')}}"></script>
<script type="text/javascript" src="{{asset('js/unidade/busca.js')}}"></script>
