<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Relatório</title>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js" integrity="sha384-w1Q4orYjBQndcko6MimVbzY0tgp4pWB4lZ7lr30WKz0vr/aWKhXdBNmNb5D92v7s" crossorigin="anonymous"></script>
</head>
<body>
    <img align="right" src="{{ public_path('imagens/logo_upe.png') }}" width="150px" height="75px">
    <h2>RELATÓRIO DE MATERIAIS EM ESTOQUE</h2>
    <h4>RELATÓRIO REFERENTE AO PERÍODO: {{ date('d/m/Y',  strtotime($datas[0])) }} A {{ date('d/m/Y',  strtotime($datas[1])) }}</h4>

    <table id="tableMateriais" style="">
        <thead style="background-color: lightgray; border-radius: 15px">
             <tr>
                <!--<th class="align-middle" scope="col">Imagem</th>-->
                <th class="align-middle" scope="col">Depósito</th>
                <th class="align-middle" scope="col">Código</th>
                <th class="align-middle" scope="col" style="text-align: center" width="340px">Descrição</th>
                <th class="align-middle" scope="col">Unidade</th>
                <th class="align-middle" scope="col">Quantidade</th>
            </tr>
        </thead>
        <tbody>
            @if(count($materiais) > 0)
                @foreach($materiais as $key => $material)
                    @for($i = 0; $i < count($material); $i++)
                        <tr style="background-color: @if($i%2 == 1)#ddd @endif">
                            <!--<td class="align-middle" scope="col" style="text-align: center">{{$material[$i]->imagem}}</td>-->
                            <td class="align-middle" scope="col" style="text-align: center">{{$key}}</td>
                            <td class="align-middle" scope="col" style="text-align: center">{{$material[$i]->codigo}}</td>
                            <td class="align-middle" scope="col" style="text-align: center">{{$material[$i]->nomemat}} - {{$material[$i]->descricao}}</td>
                            <td class="align-middle" scope="col" style="text-align: center">Und</td>
                            <td class="align-middle" scope="col" style="text-align: center">{{$material[$i]->quantidade}}</td>
                        </tr>
                    @endfor

                @endforeach
            @endif
        </tbody>
    </table>
</body>
</html>
