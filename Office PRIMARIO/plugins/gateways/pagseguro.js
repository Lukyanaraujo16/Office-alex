if(typeof $ === "undefined"){
  $ = jQuery.noConflict();
}

$(function () {
  $("button.gateway-pagseguro").on("click", (e) => {
    e.preventDefault();

    if(typeof PagSeguroLightbox !== "undefined"){
      $.get( "/sys/clientApi.php?action=start_gateway_pagseguro", function( { id } ) {
        let isOpenLightbox = PagSeguroLightbox(id, {
          success : function(transactionCode) {
            //Insira os comandos para quando o usuário finalizar o pagamento. 
            //O código da transação estará na variável "transactionCode"
           alert("Compra feita com sucesso, código de transação: " + transactionCode);
          },
          abort : function() {
            //Insira os comandos para quando o usuário abandonar a tela de pagamento.
           alert("abortado");
          }
        });
  
        if(!isOpenLightbox){
          location.href = `https://pagseguro.uol.com.br/v2/checkout/payment.html?code=${id}`;
        }
      })
    }
    else{
      alert("Não foi possível inicializar pagamento com pagseguro");
    }
  });
});