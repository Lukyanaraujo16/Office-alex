$(function () {
  //Initialize Select2 Elements
  $(".select2bs4").select2({
    theme: "bootstrap4",
  });
});

$(document).ready(function () {
  $(".addmessage").click(function () {
    var message = $("#message").val();
    //verifica se o campo está vazio, caso esteja ignora o click
    if ($("#message").val() != "") {
      var row = $(
        "<tr><td>" +
          message +
          '</td><td><button type="button" class="btn btn-outline-danger btn-sm float-right"><i class="far fa-times danger"></i></button></td></tr>'
      );
      $("table tbody").append(row);
      $("#message").val(""); // limpa o valor do input
      $("#message").removeClass("is-invalid");
    } else {
      $("#message").addClass("is-invalid");
    }
  });

  $("table").on("click", ".btn-outline-danger", function () {
    $(this).closest("tr").remove();
  });

  $("#rule_type").on("change", function () {
    if ($(this).val() != "") {
      $(this).removeClass("is-invalid");
    }
  });

  $("#rule_action").on("change", function () {
    if ($(this).val() != "") {
      $(this).removeClass("is-invalid");
    }
    var response = $(this).val();
    var response_array = response.split("_");
    if (response_array[1] == "iptv") {
      pasteDefaultTemplate("iptv");
    } else if (response_array[1] == "code") {
      pasteDefaultTemplate("code");
    } else if (response_array[1] == "binstream") {
      pasteDefaultTemplate("binstream");
    }
  });

  $("#response").on("change", function () {
    if ($(this).val() != "") {
      $(this).removeClass("is-invalid");
    }
  });

  $(".editrule").on("click", function () {
    //desativa botão de salvar
    $(".editrule").attr("disabled", true);
    var messages = [];
    $("table.table tbody tr").each(function () {
      var message = $(this).find("td:first-child").text();
      messages.push(message);
    });
    var rule_type = $("#rule_type").val();
    var rule_action = $("#rule_action").val();
    var response = $("#response").val();
    var rule_id = $("#rule_id").val();

    var valid = true;
    $(".required-input").each(function () {
      if ($(this).val() == "") {
        $(this).addClass("is-invalid");
        valid = false;
      } else {
        $(this).removeClass("is-invalid");
      }
    });

    if (messages.length <= 0) {
      $("#message").addClass("is-invalid");
      valid = false;
    }

    if (valid) {
      var data = {
        action: "edit_chatbot_rule",
        rule_type: rule_type,
        rule_action: rule_action,
        response: response,
        messages: messages,
        rule_id: rule_id,
      };

      $.ajax({
        url: "/sys/api.php",
        type: "POST",
        data: data,
        success: function (response) {
          if (response.success == false) {
            Swal.fire("Ops...", response.message, "warning");
          } else {
            // Swal.fire("Sucesso!", "Regra salva!", "success");
            Swal.fire({
              title: "Regra salva!",
              html: "Voltando a lista de regras...",
              icon: "success",
              timer: 5000,
              timerProgressBar: true,
            });

            setTimeout(function () {
              window.location.href = "/chatbot/list";
            }, 5000);
          }
        },
        error: function (xhr, status, error) {
          Swal.fire("Ops!", "Ocorreu um erro ao editar a regra", "error");
        },
      });
    }
  });

  function pasteDefaultTemplate(type) {
    var textarea = document.getElementById("response");
    var template = document.getElementById("server_template_" + type).value;
    textarea.value = template;
    textarea.focus();
  }
});
