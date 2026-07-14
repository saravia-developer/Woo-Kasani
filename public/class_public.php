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

    $key = 'wk_new_user_' . md5(strtolower($email));

    if ((int) $spin_meta['spin_num'] !== 1) {
      set_transient(
        $key,
        true,
        MINUTE_IN_SECONDS
      );
      return $stop;
    }

    $index = array_search(30, $wheel['coupon_amount']);
    delete_transient($key);

    return $index;
  }

  public function update_response_win_lucky_wheel($data, $email)
  {
    $key = 'wk_new_user_' . md5(strtolower($email));

    if((bool) get_transient($key)) {
      return $data;
    }

    $message = get_option('message_to_roulette_winners', '');
    $color_actual = get_option('mi_color_guardado', '#0073aa');

    $data['is_new_user'] = [ 
      'message' => $message,
      'color' => $color_actual
    ];

    delete_transient($key);

    return $data;
  }

  public function change_background_color()
  {
    $backgroud_color = get_option('mi_color_guardado', '');
    if ($backgroud_color === '') {
      return;
    }

    ?>
    <style>
      .wlwl_lucky_wheel_content.success {
        background-color:
          <?php echo sanitize_hex_color($backgroud_color); ?>
        ;
      }
    </style>
    <?php
  }

  public function mi_producto_es_oferta_flash($on_sale, $product)
  {

  }

}