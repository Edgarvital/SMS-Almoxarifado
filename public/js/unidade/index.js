$(function () {
    $('.numTel').mask('(00)00000-0000');

    $('#tableUnidadeIndex').DataTable({
        searching: true,
        "language": {
            "search": "Pesquisar:",
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "info": "Exibindo página _PAGE_ de _PAGES_",
            "infoEmpty": "Nenhum registro disponível",
            "zeroRecords": "Nenhum registro disponível",
            "paginate": {
                "previous": "Anterior",
                "next": "Próximo"
            }
        },
        "columnDefs": [{
            "targets": [6],
            "orderable": false
        }]
    });

    $('#tableUnidadeIndex').on('page.dt', function () {
        $('html, body').animate({
            scrollTop: $(".dataTables_wrapper").offset().top
        }, 'fast');
    });

    $('#tableUnidadeIndex').DataTable().columns().iterator('column', function (ctx, idx) {
        $($('#tableUnidadeIndex').DataTable().column(idx).header()).append('<span class="sort-icon"/>');
    });

});
