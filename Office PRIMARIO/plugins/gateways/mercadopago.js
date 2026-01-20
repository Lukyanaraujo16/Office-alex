if(typeof $ === "undefined"){
  $ = jQuery.noConflict();
}

$(function () {
  $("button.gateway-mercadopago").on("click", (e) => {
    e.preventDefault();

    if(typeof mercadopago !== "undefined"){
      $.get( "/sys/clientApi.php?action=start_gateway_mercadopago", function( { id } ) {
        mercadopago.checkout({
          preference: { id },
          render: {
            container: '.cho-container', // Indica onde o botão de pagamento será exibido
            label: 'Pagar', // Muda o texto do botão de pagamento (opcional)
          }
        });
      })
    }
    else{
      alert("Não foi possível inicializar pagamento com mercado pago");
    }
  });
});