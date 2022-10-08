$(function () {

    $('.selectMaterial').select2({
        placeholder: "Digite algo ou selecione uma opção."
    });

    $("#quantMaterial").mask("#", {
        maxlength: true,
        translation: {
            '#': {pattern: /[0-9]/, recursive: true}
        }
    });

});

function setValuesRowInput() {
    var materiais = [];
    var quantidades = [];
    var unidades = [];

    var escolha = confirm("Tem certeza que deseja fazer uma solicitação?");

    $("#tableMaterial > tbody > tr").children('.materialRow').each(function () {
        materiais.push($(this).data('id'));
    });

    $("#tableMaterial > tbody > tr").children('.quantidadeRow').each(function () {
        quantidades.push($(this).text());
    });

    $("#tableMaterial > tbody > tr").children('.unidadeRow').each(function () {
        unidades.push($(this).data('id'));
    });

    $('#dataTableMaterial').val([materiais]);
    $('#dataTableQuantidade').val([quantidades]);
    $('#dataTableUnidade').val([unidades]);
}
