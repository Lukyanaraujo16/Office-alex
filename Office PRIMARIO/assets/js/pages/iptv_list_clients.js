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
    ajax: "/sys/api.php?action=get_clients",
    processing: true,
    serverSide: true,
    columns: [
      {
        data: "id",
      },
      {
        data: "display_username",
      },
      {
        data: "password",
      },
      {
        data: "email",
      },
      {
        data: "created_at",
      },
      {
        data: "exp_date",
      },
      {
        data: "reseller_name",
      },
      {
        data: "max_connections",
      },
      {
        data: "reseller_notes",
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
        targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
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
    drawCallback: function () {
      $('[data-toggle="tooltip"]').tooltip();
    },
  });

  $(document).on("click", ".btrefresh", function (e) {
    table.ajax.reload();
    toastr.info("Recarregando tabela");
  });
  /* ADICIONAR TELA */
  $(document).on("click", ".bttela", function (e) {
    e.preventDefault();
    const id = $(this).data("id");

    bootbox.dialog({
      title: "Tem certeza que deseja adicionar mais uma tela ?",
      message: "<p>" + $(this).data("text") + "</p>",
      buttons: {
        cancel: {
          label: "Cancelar",
          className: "btn-secondary",
          callback: function () {},
        },
        noclose: {
          label: "Confirmar",
          className: "btn-info btnaddscreen",
          callback: function () {
            $(".btnaddscreen").hide();

            $.get(
              "/sys/api.php?action=add_screen&client_id=" + id,
              function (data) {
                if (data.result === "success") {
                  table.ajax.reload();
                  toastr.success("Máximo de conexões aumentada com sucesso!");
                } else {
                  toastr.warning(
                    "Não foi possível aumentar o máximo de conexões do cliente."
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

  // Fast message
  $(document).on("click", ".btfastmessage", function (e) {
    e.preventDefault();
    const id = $(this).data("id");

    showFastMessage(id);
  });

  function showFastMessage(id) {
    bootbox.dialog({
      message:
        '<p class="text-center mb-0"><i class="fa fa-spin fa-cog"></i> Carregando...</p>',
      closeButton: false,
    });

    $.post(
      "/sys/api.php?action=fast_message&type=iptv&client_id=" + id,
      function (data) {
        bootbox.hideAll();
        $(".bootbox.modal").remove();
        $(".modal-backdrop").remove();

        if (data.result == "success") {
          const fast_message =
            '<div class="fast-message">' +
            data.message.replace(/(?:\r\n|\r|\n)/g, "<br>") +
            '</div><button type="button" class="btn copy-fast-message d-none" data-clipboard-target=".fast-message">Hide Button ;)</button>';

          bootbox.dialog({
            message: fast_message,
            //size: "large",
            buttons: {
              noclose2: {
                label: "Whatsapp",
                className: "btn-success waves-effect waves-light",
                callback: function () {
                  const message = encodeURIComponent(
                    data.message.replace(/<br\s*[\/]?>/gi, "")
                  );

                  const destination =
                    "https://api.whatsapp.com/send?phone=&text=" + message;

                  const win = window.open(destination, "_blank");
                  if (win) {
                    win.focus();
                  } else {
                    window.location.href = destination;
                  }
                  return false;
                },
              },
              noclose: {
                label: "Copiar",
                className: "btn-primary bg-gradient waves-effect waves-light",
                callback: function () {
                  $(".copy-fast-message").click();
                  return false;
                },
              },
              cancel: {
                label: "Fechar",
                className: "btn-secondary waves-effect waves-light",
                callback: function () {},
              },
            },
          });
          new ClipboardJS(".copy-fast-message");
        }
      },
      "JSON"
    );
  }

  /* RENOVAR VARIOS MESES CLIENTE */
  $(document).on("click", ".btrenewplus", function (e) {
    e.preventDefault();
    const id = $(this).data("id");

    bootbox.dialog({
      title: "Tem certeza que deseja renovar este cliente ?",
      message:
        "<p>" +
        $(this).data("text") +
        '</p><form class="form-horizontal">' +
        '<div class="form-group col-md-6"><label class="form-control-label">Quantidade de meses</label><div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar-plus-o"></i></span><input type="number" class="form-control" required="" value="1" autocomplete="off" id="months" name="months"></div></div>' +
        '<div class="form-group row">' +
        '<div class="col-md-12"><span class="text-white">Escolha a quantidade de meses.<br><br><b>Fique atento, caso seja um usuario de 2 telas irá cobrar o dobro de créditos equivalente a quantidade de meses.</b></span></div>' +
        "</div></form>",
      buttons: {
        cancel: {
          label: "Cancelar",
          className: "btn-secondary",
          callback: function () {},
        },
        noclose: {
          label: "Confirmar",
          className: "btn-info btnrenewplus",
          callback: function () {
            $(".btnrenewplus").hide();

            const months = $("#months").val();
            if (months > 0) {
              $.get(
                "/sys/api.php?action=renew_client_plus&client_id=" +
                  id +
                  "&months=" +
                  months,
                function (data) {
                  if (data.result === "success") {
                    table.ajax.reload();
                    toastr.success("Cliente renovado com sucesso!");
                  } else {
                    toastr.warning("Não foi possível renovar o cliente.");
                  }
                },
                "json"
              );
            } else {
              toastr.warning("Quantidade de meses inválida.");
            }
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
      title: "Tem certeza que deseja bloquear/desbloquear este usuário ?",
      message: "<p>" + $(this).data("text") + "</p>",
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
            $.get(
              "/sys/api.php?action=toggle_block_client&user_id=" + id,
              function (data) {
                if (data.result === "success") {
                  table.ajax.reload();
                  toastr.success("Usuário bloqueado/desbloqueado com sucesso!");
                } else {
                  toastr.warning(
                    "Não foi possível bloquear/desbloquear este usuário."
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
      title: "Tem certeza que deseja deletar este usuário ?",
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
              "/sys/api.php?action=delete_client&user_id=" + id,
              function (data) {
                if (data.result === "success") {
                  table.ajax.reload();
                  toastr.success("Usuário deletado com sucesso!");
                } else {
                  toastr.warning("Não foi possível deletar este usuário.");
                }
              },
              "json"
            );
          },
        },
      },
    });
  });

  /* CONVERTER PARA P2P */
  $(document).on("click", ".btconvert", function (e) {
    e.preventDefault();
    const id = $(this).data("id");

    bootbox.hideAll();

    bootbox.dialog({
      title: "Tem certeza que deseja converter para P2P?",
      message: "<p>" + $(this).data("text") + "</p>",
      buttons: {
        cancel: {
          label: "Cancelar",
          className: "btn-secondary waves-effect waves-light",
          callback: function () {},
        },
        noclose: {
          label: "Confirmar",
          className:
            "btn-primary bg-gradient waves-effect waves-light btnconvert",
          callback: function () {
            $(".btnconvert").hide();

            bootbox.dialog({
              message:
                '<p class="text-center mb-0"><i class="fas fa-spinner fa-spin"></i> Carregando... Por favor aguarde.</p>',
              closeButton: false,
            });

            $.get(
              "/sys/api.php?action=convert&user_id=" + id + "&from=iptv",
              function (data) {
                if (data.result === "success") {
                  table.ajax.reload();
                  toastr.success("IPTV convertido para P2P com sucesso!");
                } else if (data.result === false) {
                  toastr.warning(data.message);
                } else {
                  toastr.warning("Não foi possível converter este usuário.");
                }
                bootbox.hideAll();
              },
              "json"
            );
          },
        },
      },
    });
  });

  const server_dns = "<?php echo getServerDNS(); ?>";
  const shortener_url = "<?php echo OFFICE_CONFIG['shorten_url']; ?>";
  const ssiptv_url = "<?php echo OFFICE_CONFIG['ssiptv_url']; ?>";
  const reseller_id = "<?php echo $logged_user['id']; ?>";
  const dns_name = server_dns.split("//");

  const custom_dns_html =
    "<option value='" + server_dns + "'>" + dns_name[1] + "</option>\n\r";
  const list_type_html =
    "<option value='type=m3u_plus&output=ts'>M3U Plus</option>\n\r<option value='type=m3u&output=ts'>M3U</option>\n\r<option value='type=m3u_plus&output=hls'>HLS Plus</option>\n\r<option value='type=m3u&output=hls'>HLS</option>\n\r<option value='SSIPTV'>SSIPTV 📺</option>\n\r";
  const ssiptv_dns_html =
    "<option value='" + ssiptv_url + "/ssiptv/'>Link SSIPTV</option>\n\r";

  $(document).on("click", ".btlink", function (e) {
    e.preventDefault();

    const user = $(this).data("user");
    const pass = $(this).data("pass");

    var dialog = bootbox.dialog({
      size: "large",
      title: "Gerar Link",
      message:
        '<form id="download_list" class="form-horizontal"> <div class="form-group row"> <div class="col-md-8"> <label class="form-control-label">Escolha o DNS:</label> <div class="input-group"> <select class="form-control" required="" autocomplete="off" id="c_dns"> </select> </div> </div> <div class="col-md-4"> <label class="form-control-label">Tipo:</label> <div class="input-group"> <select class="form-control" required="" autocomplete="off" id="c_type"> </select> </div> </div> </div> <div class="form-group mt-3 row"> <div class="col-md-12"> <label class="form-control-label">Seu link:</label> <div class="input-group"> <input type="text" class="form-control" required="" autocomplete="off" id="list_link" readonly> <div class="input-group-append"> <button type="button" class="btn btn-sm btn-primary bg-gradient waves-effect waves-light copylinklist" data-clipboard-target="#list_link">COPIAR</button> </div> </div> </div> </div> </form>',
      buttons: {
        noclose: {
          label: "Encurtar",
          className:
            "btn-success bg-gradient waves-effect waves-light btshorten",
          callback: function () {
            $(".btshorten").hide();

            $.get(
              shortener_url,
              {
                url: $("#list_link").val(),
                creator_id: reseller_id,
                format: "text",
              },
              function (data) {
                $("#list_link").val(data);
              }
            );
            return false;
          },
        },
        cancel: {
          label: "Fechar",
          className: "btn-secondary waves-effect waves-light",
          callback: function () {},
        },
      },
    });

    dialog.init(function () {
      $("#c_dns").html(custom_dns_html);
      $("#c_type").html(list_type_html);

      loadList();

      new ClipboardJS(".copylinklist");
    });

    $("body").on("change", "select", function () {
      loadList();
    });

    var previus_type = "";

    function loadList() {
      if ($("#c_type").val() != "SSIPTV") {
        if (previus_type == "" || previus_type == "SSIPTV") {
          $("#c_dns").html(custom_dns_html);
        }
        if (previus_type == "") {
          $("#c_dns").html(custom_dns_html);
          $("#c_type").html(list_type_html);
        }

        const list =
          $("#c_dns").val() +
          "/get.php?username=" +
          user +
          "&password=" +
          pass +
          "&" +
          $("#c_type").val();
        $("#list_link").val(list);
      } else {
        $("#c_dns").html(ssiptv_dns_html);

        const list =
          $("#c_dns").val() + "get/" + user + "/" + pass + "/download_m3u/";
        $("#list_link").val(list);
      }

      $(".btshorten").show();
      previus_type = $("#c_type").val();
    }
  });

  const urlParams = new URLSearchParams(window.location.search);
  const clientId = urlParams.get("client_id");
  if (clientId && clientId != "") {
    showFastMessage(clientId);
  } else {
    if (
      window.location.pathname.split("/")[3] == "show" &&
      window.location.pathname.split("/")[4] != ""
    ) {
      showFastMessage(window.location.pathname.split("/")[4]);
    }
  }
});
