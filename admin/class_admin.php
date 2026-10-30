<?php
namespace WooKasani\Admin;

require_once WOO_KASANI_CONTROLLER_DIR . 'promotions.php';
require_once WOO_KASANI_ADMIN_MENU_DIR . 'custom-response-win-lucky.php';

use WooKasani\Admin\Menu\CustomWinLucky;
use WooKasani\Admin\Controller\PromotionControllers;

class Admin
{
  public $custom_win_lucky;

  public function __construct()
  {
    $this->custom_win_lucky = new CustomWinLucky();
  }

  public function add_menu()
  {
    $this->custom_win_lucky->add_menu();
  }

  public function on_dependence($hook)
  {
    $this->custom_win_lucky->cargar_scripts_color_picker($hook);
  }

  /*
   * Add toggle button to promotions columns 
   */
  public function add_toggle_btn_to_promotions_columns($columns)
  {
    $new_columns = [];
    foreach ($columns as $key => $value) {
      $new_columns[$key] = $value;
      if ($key == 'title') {
        $new_columns['promotion_active'] = 'Promo active';
      }
    }

    if (empty($new_columns['promotion_active'])) {
      $new_columns['promotion_active'] = 'Promo active';
    }
    return $new_columns;
  }

  public function display_column_promotion($column_name, $post_id)
  {

    if ($column_name != 'promotion_active') {
      return;
    }

    $active = get_post_meta($post_id, '_promotion_active', true);
    $checked = ($active == 'yes') ? 'checked' : '';
    $nonce = wp_create_nonce('toggle_promotion_nonce');

    require WOO_KASANI_TEMPLATE_DIR . 'custom_columns/custom_columns.php';
  }

  public function ajax_toggle_promotion()
  {
    // Verificar nonce
    if (
      !isset($_POST['nonce']) ||
      !wp_verify_nonce($_POST['nonce'], 'toggle_promotion_nonce')
    ) {
      wp_send_json_error('Nonce inválido');
    }

    $post_id = intval($_POST['post_id']);
    $active = intval($_POST['active']);

    // Verificar permisos (opcional)
    if (!current_user_can('edit_post', $post_id)) {
      wp_send_json_error('No tienes permisos');
    }


    // Actualizar metadato
    // active 1
    // no active 0
    if ($active == '1') {
      PromotionControllers::deactive_promotions_active($post_id);

      update_post_meta($post_id, '_promotion_active', 'yes');
    } else {

      update_post_meta($post_id, '_promotion_active', 'no');
    }

    wp_send_json_success('Actualizado');
  }


  /**
   * UPDATE PRODUCTS FOR FLASH SALE
   */

  public function data_of_promotion_active()
  {
    $offers = PromotionControllers::get_promotions_active(['fields' => 'ids']);

    if (empty($offers->posts)) {
      return [];
    }

    $id = $offers->posts[0];

    $products_flash_sale_json = get_post_meta($id, '_products_selected', true);
    $flash_sale_discount = get_post_meta($id, '_discount_quantity', true);
    $type_discount = get_post_meta($id, '_select_type_discount', true);
    $products_flash_sale = json_decode($products_flash_sale_json);

    if (!is_array($products_flash_sale)) {
      $products_flash_sale = [];
    }

    return [
      'discount' => $flash_sale_discount,
      'products' => $products_flash_sale,
      'type_discount' => $type_discount
    ];
  }

  public function get_data_of_promotion_active()
  {
    $data = get_option('data_of_promotion_active');

    if (empty($data)) {
      $data = $this->data_of_promotion_active();
      update_option('data_of_promotion_active', $data);
    }

    $data = is_string($data)
      ? json_decode($data, true)
      : $data;

    $data = is_array($data)
      ? $data
      : [];

    return $data;
  }

  /**************************************/
  /****** MODIFICADORES DE PRECIOS ******/
  /**************************************/
  public function woo_kasani_apply_promotion_discount($price, $product)
  {
    // Solo en frontend (no en admin ni en REST)
    if (is_admin() && !wp_doing_ajax()) {
      return $price;
    }

    $data = $this->get_data_of_promotion_active();
    if (empty($data['products']) || $data['discount'] <= 0) {
      return $price;
    }

    $ids = wp_list_pluck($data['products'], 'id');

    // Verificar si el producto está en la lista
    if (!in_array($product->get_id(), $ids)) {
      return $price;
    }

    // Obtener precio regular
    // $regular_price = (float) $product->get_regular_price();
    $regular_price = (float) $product->get_regular_price();

    if ($regular_price <= 0) {
      return $price;
    }

    switch ($data['type_discount']) {
      case 'fixed_product':
        $new_price = $regular_price - $data['discount'];
        break;
      case 'percentage':
        $new_price = $regular_price * (1 - $data['discount'] / 100);
        break;
      default:
        return round((int) $price, 0);
    }

    return round($new_price, 2);
  }

  public function woo_kasani_get_price($price, $product)
  {
    $sale_price = $product->get_sale_price();

    if ($sale_price !== '') {
      return $sale_price;
    }

    return $price;
  }

  public function woo_kasani_set_product_on_sale($on_sale, $product)
  {
    if (is_admin() && !wp_doing_ajax()) {
      return $on_sale;
    }

    $data = PromotionControllers::get_promotion_meta() ?? [];
    $promotion_end_time = '';

    foreach ($data as $row) {
      if ($row->meta_key === '_promotion_end_time') {
        $promotion_end_time = $row->meta_value;
        break;
      }
    }

    if (intval($promotion_end_time) < current_time('timestamp')) {
      return $on_sale;
    }

    $data = $this->get_data_of_promotion_active();
    if (empty($data['products'])) {
      return $on_sale;
    }

    $ids = wp_list_pluck($data['products'], 'id');

    if (in_array($product->get_id(), $ids)) {
      return true;
    }

    return $on_sale;
  }

  public function woo_kasani_custom_sale_flash($html, $post, $product)
  {
    if (is_admin() && !wp_doing_ajax()) {
      return $html;
    }
    error_log(__METHOD__);

    $data = $this->get_data_of_promotion_active();
    if (empty($data['products'])) {
      return $html;
    }

    $ids = wp_list_pluck($data['products'], 'id');

    if (in_array($product->get_id(), $ids)) {
      // Reemplazar el badge por completo
      // return '<span class="flash-sale-badge">Flash Sale</span>';
      return '<span class="onsale flash-sale-badge">Flash Sale</span>';
    }

    // return $html;
    return $html . '<span class="flash-sale-badge">Flash Sale</span>';
    ;
  }

  public function woo_kasani_custom_sale_flash_block_edit($text, $product)
  {
    if (is_admin() && !wp_doing_ajax()) {
      return $text;
    }

    $data = $this->get_data_of_promotion_active();

    if (empty($data['products'])) {
      return $text;
    }

    $ids = wp_list_pluck($data['products'], 'id');

    if (in_array($product->get_id(), $ids, true)) {
      return 'Flash Sale';
    }

    return $text;
  }



  /*****************************************/
  /***** ACTUALIZADOR POR FINALIZACIÓN *****/
  /*****************************************/
  public function add_flash_warning_and_timer()
  {
    // Buscar la oferta flash activa más próxima a terminar (la que tiene la fecha fin más cercana)
    $offers = PromotionControllers::get_promotions_active(['fields' => 'ids']);

    if (empty($offers->have_posts()))
      return;

    $offer = $offers->posts[0];

    $start_date = get_post_meta($offer, '_promotion_start_time', true);
    $end_date = get_post_meta($offer, '_promotion_end_time', true);
    $image_url = get_the_post_thumbnail_url($offer, 'medium');

    $timestamp_start = $start_date;
    $timestamp_end = $end_date;
    ?>
	<span class="close-flash-warning">&times;</span>
    <div onclick="location.href='https://kasani.pe/producto/langostino-jumbo-2/'" class="flash-warning-container">
      <div class="message-flash-warning">
        <!-- <span>
          ¡Aprovecha nuestra ofertas flash!
        </span> -->
        <img 
          src="<?= $image_url; ?>"
          alt="flash-sale-image"
        />
      </div>

      <div id="flash-countdown" data-start="<?php echo esc_attr($timestamp_start); ?>"
        data-end="<?php echo esc_attr($timestamp_end); ?>">
        <span class="days">00</span><span style="font-size:16px; color:#EFEFEF;">d</span>
        <span class="hours">00</span><span style="font-size:16px; color:#111111;">h</span>
        <span class="minutes">00</span><span style="font-size:16px; color:#111111;">m</span>
        <span class="seconds">00</span><span style="font-size:16px; color:#111111;">s</span>
      </div>
    </div>

    <?php
    // Encolar el script del contador SOLO si hay oferta activa
    add_action('wp_footer', [$this, 'promotion_script_contador']);
  }

  public function promotion_script_contador()
  {
    // Para evitar encolarlo múltiples veces, usamos un flag
    if (did_action('mi_contador_encolado'))
      return;
    do_action('mi_contador_encolado');

    ?>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('flash-countdown');
        if (!container) return;

        const startTimestamp = parseInt(container.dataset.start) * 1000; // a milisegundos
        const endTimestamp = parseInt(container.dataset.end) * 1000; // a milisegundos

        const intervalPromoCount = setInterval(updateCountdown, 1000);

        function updateCountdown() {
          const now = new Date().getTime();
          let distance = endTimestamp - now;

          if (now < startTimestamp) {
            container.innerHTML = '';
            return;
          } else if (startTimestamp <= now && now < endTimestamp) {
            container.innerHTML = `
                <span class="days">00</span><span style="font-size:16px; color:#fff;">d</span>
                <span class="hours">00</span><span style="font-size:16px; color:#fff;">h</span>
                <span class="minutes">00</span><span style="font-size:16px; color:#fff;">m</span>
                <span class="seconds">00</span><span style="font-size:16px; color:#fff;">s</span>
              `;

            const changeEvent = new CustomEvent('flash_sale_countdown_change');
            container.dispatchEvent(changeEvent);

          } else if (endTimestamp < now) {
            container.innerHTML = '🔥 ¡OFERTA TERMINADA!';
            clearInterval(intervalPromoCount);
            return;
          }

          const days = Math.floor(distance / (1000 * 60 * 60 * 24));
          const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
          const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
          const seconds = Math.floor((distance % (1000 * 60)) / 1000);

          container.querySelector(`.days`).textContent = String(days).padStart(2, '0');
          container.querySelector(`.hours`).textContent = String(hours).padStart(2, '0');
          container.querySelector(`.minutes`).textContent = String(minutes).padStart(2, '0');
          container.querySelector(`.seconds`).textContent = String(seconds).padStart(2, '0');
        }
      });
    </script>
    <?php
  }
  /*****************************************/
  /***** ACTUALIZADOR POR FINALIZACIÓN *****/
  /*****************************************/
  public function frontend_drop_sale_price_for_promotions()
  {
    if (
      !isset($_POST['nonce']) ||
      !wp_verify_nonce(
        $_POST['nonce'],
        'deactivate_flash_promotion_nonce'
      )
    ) {
      wp_send_json_error('Nonce inválido');
    }

    $data = PromotionControllers::get_promotion_meta();

    if (empty($data)) {
      return;
    }

    $id = $data[0]->post_id;
    $products_of_promotion = [];
    $promotion_end_time = '';

    foreach ($data as $row) {
      switch ($row->meta_key) {
        case '_products_selected':
          $products_of_promotion = $row->meta_value;
          break;
        case '_promotion_end_time':
          $promotion_end_time = $row->meta_value;
          break;
      }
    }

    $products_of_promotion = json_decode(
      $products_of_promotion,
      true
    );

    if (!is_array($products_of_promotion)) {
      $products_of_promotion = [];
    }

    if ((int) $promotion_end_time < current_time('timestamp')) {
      if (empty($products_of_promotion)) {
        $products_of_promotion = [];
      }

      $ids = wp_list_pluck($products_of_promotion, 'id');

      $products = wc_get_products(['include' => $ids]);

      if (empty($products)) {
        return;
      }

      foreach ($products as $product) {
        // Eliminar el precio de oferta
        $product->set_sale_price(null);
        $product->set_price($product->get_regular_price());
        $product->save();
      }

      update_post_meta($id, '_promotion_active', 'no');
    }
  }
}