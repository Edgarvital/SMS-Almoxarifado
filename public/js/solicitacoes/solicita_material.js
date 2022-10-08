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
    var escolha = confirm("Tem certeza que deseja fazer uma solicitação?");
}
