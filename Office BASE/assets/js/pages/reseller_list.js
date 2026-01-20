$(function () {
  toastr.options = {
    closeButton: false,
    debug: false,
    newestOnTop: true,
    progressBar: true,
    positionClass: "toast-top-right",
    preventDuplicates: false,
    onclick: null,
    showDuration: "300",
    hideDuration: "1000",
    timeOut: "5000",
    extendedTimeOut: "1000",
    showEasing: "swing",
    hideEasing: "linear",
    showMethod: "fadeIn",
    hideMethod: "fadeOut",
  };

  var table = $("#table").DataTable({
    ajax: "./sys/api.php?action=get_resellers",
    processing: true,
    serverSide: true,
    columns: [
      {
        data: "id",
      },
      {
        data: "username",
      },
      {
        data: "email",
      },
      {
        data: "group",
      },
      {
        data: "ip",
      },
      {
        data: "credits",
      },
      {
        data: "reseller_name",
      },
      {
        data: "notes",
      },
      {
        data: "status",
      },
      {
        data: "action",
      },
    ],
    columnDefs: [
      {
        targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
        className: "text-center",
      },
    ],
    order: [[0, "desc"]],
    paging: true,
    lengthChange: true,
    searching: true,
    ordering: true,
    orderMulti: false,
    info: true,
    autoWidth: false,
    lengthMenu: [
      [10, 25, 50, 100, 500, 1000],
      [10, 25, 50, 100, 500, 1000],
    ],
    language: {
      processing: "Processando...",
      lengthMenu: "Mostrar _MENU_ registros",
      zeroRecords: "Não foram encontrados resultados",
      info: "Mostrando de _START_ até _END_ de _TOTAL_ registros",
      infoEmpty: "Mostrando de 0 até 0 de 0 registros",
      sInfoFiltered: "",
      sInfoPostFix: "",
      search: "Buscar:",
      url: "",
      loadingRecords: "Carregando...",
      paginate: {
        first: "Primeiro",
        previous: "<i class='fas fa-chevron-left'></i>",
        next: "<i class='fas fa-chevron-right'></i>",
        last: "Último",
      },
    },
    initComplete: function () {
      // Configura o evento de mudança no elemento de seleção
      // $('#select-status').change(function() {
      $("#select-status, #select-type, #select-reseller").on(
        "change",
        function () {
          var status = $("#select-status").val();
          var type = $("#select-type").val();
          var reseller = $("#select-reseller").val();
          atualizaTabela(status, type, reseller);
        }
      );
    },
    drawCallback: function () {
      $('[data-toggle="tooltip"]').tooltip();
    },
  });

  var searchInput = $("div.dataTables_filter input").detach();
  searchInput.appendTo("#search-div");
  var searchInput = $("div.dataTables_filter label").hide();

  $(".select2").select2();

  function atualizaTabela(status, type, reseller) {
    var url =
      "/sys/api.php?action=get_resellers&status=" +
      status +
      "&type=" +
      type +
      "&reseller=" +
      reseller;

    table.ajax.url(url).load();
  }

  $(document).on("click", ".btrefresh", function (e) {
    var status = $("#select-status").val();
    var type = $("#select-type").val();
    var reseller = $("#select-reseller").val();
    atualizaTabela(status, type, reseller);
    toastr.info("Recarregando tabela");
  });
  /* ADICIONAR/REMOVER CREDITOS */
  $(document).on("click", ".btcredits", function (e) {
    e.preventDefault();
    const id = $(this).data("id");
    bootbox.dialog({
      title: "Adic/Remover créditos",
      message:
        "<p>" +
        $(this).data("text") +
        '</p><form class="form-horizontal">' +
        '<div class="form-group col-md-6"><label class="form-control-label">Quantidade de créditos</label><div class="input-group"><span class="input-group-addon"><i class="fa fa-dollar"></i></span><input type="number" class="form-control" required="" value="0" autocomplete="off" id="credits" name="credits"></div></div>' +
        '<div class="form-group row">' +
        '<div class="col-md-12"><span class="text-blue">Escolha a quantidade de créditos.<br><b>*Para retirar créditos coloque o sinal de menos na frente.</b></span></div>' +
        "</div></form>",
      buttons: {
        cancel: {
          label: "Cancelar",
          className: "btn-secondary",
          callback: function () {},
        },
        noclose: {
          label: "Confirmar",
          className: "btn-info btncredits",
          callback: function () {
            $(".btncredits").hide();
            const credits = $("#credits").val();
            $.get(
              "./sys/api.php?action=change_credits&reseller_id=" +
                id +
                "&credits=" +
                credits,
              function (data) {
                if (data.result === "success") {
                  var status = $("#select-status").val();
                  var type = $("#select-type").val();
                  var reseller = $("#select-reseller").val();
                  atualizaTabela(status, type, reseller);
                  toastr.success(
                    "Os créditos foram adicionados/removidos com sucesso!"
                  );
                } else {
                  toastr.warning(
                    "Não foi possível adicionar/remover os créditos, verifique se a quantia é válida."
                  );
                }
              },
              "json"
            );
          },
        },
      },
    });
  });

  /* BLOQUEAR/DESBLOQUEAR */
  $(document).on("click", ".btblock", function (e) {
    e.preventDefault();
    const id = $(this).data("id");
    bootbox.dialog({
      title: "Tem certeza que deseja " + $(this).data("text"),
      message:
        '<div class="custom-control custom-switch">' +
        '<input type="checkbox" class="custom-control-input" name="allBelow" id="allBelow">' +
        '<label class="custom-control-label" for="allBelow">Revendas abaixo<br><small>Se marcado será bloqueada toda a árvore desse revendendor</small></label>' +
        "</div>" +
        '<div class="custom-control custom-switch mt-2">' +
        '<input type="checkbox" class="custom-control-input" name="blockClients" id="blockClients">' +
        '<label class="custom-control-label" for="blockClients">Clientes<br><small>Se marcado a ação também afetará clientes</small></label>' +
        "</div>",
      buttons: {
        cancel: {
          label: "Cancelar",
          className: "btn-secondary",
          callback: function () {},
        },
        noclose: {
          label: "Confirmar",
          className: "btn-warning btnblock",
          callback: function () {
            $(".btnblock").hide();
            const allBelow = $("#allBelow").prop("checked") ? true : false;
            const blockClients = $("#blockClients").prop("checked")
              ? true
              : false;
            const requestData = {
              reseller_id: id,
              all_below: allBelow,
              block_clients: blockClients,
            };

            $.get(
              "./sys/api.php?action=toggle_block_reseller",
              requestData,
              function (data) {
                if (data.result === "success") {
                  var status = $("#select-status").val();
                  var type = $("#select-type").val();
                  var reseller = $("#select-reseller").val();
                  atualizaTabela(status, type, reseller);
                  toastr.success(
                    "Revendedor bloqueado/desbloqueado com sucesso!"
                  );
                } else {
                  toastr.warning(
                    "Não foi possível bloquear/desbloquear este revendedor."
                  );
                }
              },
              "json"
            );
          },
        },
      },
    });
  });
  /* DELETAR */
  $(document).on("click", ".btdelete", function (e) {
    e.preventDefault();
    const id = $(this).data("id");
    bootbox.dialog({
      title: "Tem certeza que deseja deletar esta revenda ?",
      message: "<p>" + $(this).data("text") + "</p>",
      buttons: {
        cancel: {
          label: "Cancelar",
          className: "btn-secondary",
          callback: function () {},
        },
        noclose: {
          label: "Confirmar",
          className: "btn-danger btndelete",
          callback: function () {
            $(".btndelete").hide();
            $.get(
              "./sys/api.php?action=delete_reseller&reseller_id=" + id,
              function (data) {
                if (data.result === "success") {
                  var status = $("#select-status").val();
                  var type = $("#select-type").val();
                  var reseller = $("#select-reseller").val();
                  atualizaTabela(status, type, reseller);
                  toastr.success("Revendedor deletado com sucesso!");
                } else {
                  toastr.warning("Não foi possível deletar este revendedor.");
                }
              },
              "json"
            );
          },
        },
      },
    });
  });
  $(document).on("click", ".btresellerlogin", function (e) {
    e.preventDefault();
    const id = $(this).data("id");

    bootbox.dialog({
      title: "Tem certeza?",
      message: "<p>" + $(this).data("text") + "</p>",
      buttons: {
        cancel: {
          label: "Cancelar",
          className: "btn-secondary",
          callback: function () {},
        },
        noclose: {
          label: "Confirmar",
          className: "btn-info btnresellerlogin",
          callback: function () {
            $(".btnresellerlogin").hide();
            $.get(
              "./sys/api.php?action=btnresellerlogin&reseller_id=" + id,
              function (data) {
                if (data.result === "success") {
                  //redirect to dashboard
                  window.location.href = "/dashboard";
                } else {
                  toastr.warning(
                    "Não foi possível logar como este revendedor."
                  );
                }
              },
              "json"
            );
          },
        },
      },
    });
  });
});

$(document).ready(function () {
  $(".dynamic-resellers").select2({
    language: {
      noResults: function () {
        return "Nenhum resultado encontrado";
      },
      searching: function () {
        return "Pesquisando...";
      },
      inputTooShort: function () {
        return "Digite 3 ou mais caracteres";
      },
      // Outras traduções desejadas
    },
    ajax: {
      url: "/sys/api.php?action=get_resellers_simple",
      dataType: "json",
      delay: 500,
      data: function (params) {
        return {
          search: params.term, // Termo de pesquisa digitado pelo usuário
        };
      },
      processResults: function (data) {
        console.log(data);
        return {
          results: $.map(data, function (item) {
            return {
              id: item.id,
              text: item.username,
            };
          }),
        };
      },
      cache: true,
    },
    placeholder: "Selecione um revendedor",
    minimumInputLength: 3,
  });
});
