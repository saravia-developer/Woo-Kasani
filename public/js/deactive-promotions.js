jQuery(document).ready(function ($) {
  $("#flash-countdown").on("flash_sale_countdown_change", function () {
    // Mediante una injección de datos por wp_locatize_script, entras la fecha final.

    // la tienes que comparar con la fecha actual y si la fecha final es menor que la fecha actual, ejecutar un AJAX para desactivar los productos de la promoción.

    const now = new Date().getTime();
    let promotion_end_time = 0;

    if (!togglePromotion) {
      return;
    }

    togglePromotion?.promotion_end_time?.forEach((metadata) => {
      metadata.meta_key === "_promotion_end_time"
        ? (promotion_end_time = metadata.meta_value * 1000)
        : 0;
    });

    let difference = promotion_end_time - now;

    if (0 < difference) {
      return;
    }

    if(0 === difference) {
      $.ajax({
        url: togglePromotion.ajaxurl,
        type: "POST",
        data: {
          action: "deactivate_flash_promotion_nonce",
          nonce: togglePromotion.nonce,
        },
      });
    }

  });
});
