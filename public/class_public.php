<?php
namespace WooKasani\Frontend;

class Frontend
{
  public function win_new_user_a_cupon($stop, $wheel, $email)
  {
    $exists = get_posts([
      'post_type' => 'wlwl_email',
      'title' => $email,
      'numberposts' => 1,
      'fields' => 'ids'
    ]);

    $spin_meta = get_post_meta(
        $exists[0],
        'wlwl_spin_times',
        true
    );

    if ((int) $spin_meta['spin_num'] !== 1 ) {
      return $stop;
    }

    $index = array_search(30, $wheel['coupon_amount']);

    return $index;
  }

  public function change_background_color() {
    $backgroud_color = get_option('mi_color_guardado', '');
    if($backgroud_color === '') {
      return;
    }

    ?>
      <style>
      .wlwl_lucky_wheel_content.success {
        background-color: <?php echo sanitize_hex_color($backgroud_color); ?>;
      }
      </style>
    <?php
  }

  public function add_message_of_success_design() {
    $template = get_option('message_to_roulette_winners', '');
    return apply_filters( 'the_content', $template);
  }

  public function mi_producto_es_oferta_flash($on_sale, $product) {

  }

}