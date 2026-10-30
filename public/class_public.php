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

    if (empty($exists)) {
      return $stop;
    }

    $spin_meta = get_post_meta(
      $exists[0],
      'wlwl_spin_times',
      true
    );

    $key = 'wk_new_user_' . md5(strtolower($email));

    if ((int) $spin_meta['spin_num'] === 1) {

      set_transient(
        $key,
        true,
        MINUTE_IN_SECONDS
      );

      $index = array_search(30, $wheel['coupon_amount']);

      return $index;
    }

    delete_transient($key);

    return $stop;

  }

  public function update_response_win_lucky_wheel($data, $email, $name, $mobile, $wheel_label)
  {
    $key = 'wk_new_user_' . md5(strtolower($email));
    $is_new_player = (bool) get_transient($key);

    if (!$is_new_player) {
      return $data;
    }

    $message = get_option('message_to_roulette_winners', '');
    $color_actual = get_option('mi_color_guardado', '#0073aa');

    $prepare_message = str_replace( '{{wheel_label}}', $wheel_label, $message);
    $prepare_message = str_replace( '{{email}}', $email, $prepare_message);

    $data['is_new_user'] = [
      'message' => $prepare_message,
      'color' => $color_actual,
      'is_new_player' => $is_new_player
    ];

    delete_transient($key);

    // file_put_contents(
    //   WP_CONTENT_DIR . '/debug-wk.log',
    //   print_r([
    //     'data' => $data,
    //     'email' => $email,
    //     'name' => $name,
    //     'mobile' => $mobile,
    //     'wheel_label' => $wheel_label
    //   ], true) . PHP_EOL,
    //   FILE_APPEND
    // );

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
}