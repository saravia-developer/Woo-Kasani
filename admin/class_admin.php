<?php
namespace WooKasani\Admin;

require_once WOO_KASANI_CONTROLLER_DIR . 'promotions.php';
require_once WOO_KASANI_ADMIN_MENU_DIR . 'custom-response-win-lucky.php';

use WooKasani\Admin\Menu\CustomWinLucky;
use WooKasani\Admin\Controller\PromotionControllers;

class Admin
{
  public $custom_win_lucky;

  public function __construct() {
    $this->custom_win_lucky = new CustomWinLucky();
  }

  public function add_menu() {
    $this->custom_win_lucky->add_menu();
  }

  public function on_dependence($hook) {
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

    if (empty($offers->have_posts())) {
      return [];
    }

    $id = $offers->posts[0];

    $products_flash_sale_json = get_post_meta($id, '_products_selected', true);
    $flash_sale_discount = get_post_meta($id, '_discount_quantity', true);
    $type_discount = get_post_meta($id, '_select_type_discount', true);
    $products_flash_sale = json_decode($products_flash_sale_json);

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

    return $data;
  }

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
    $regular_price = (float) $product->get_regular_price();
    if ($regular_price <= 0) {
      return $price;
    }

    // Calcular nuevo precio con descuento
    $new_price = $regular_price * (1 - $data['discount'] / 100);

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

    $data = $this->get_data_of_promotion_active();
    if (empty($data['products'])) {
      return $html;
    }

    $ids = wp_list_pluck($data['products'], 'id');

    if (in_array($product->get_id(), $ids)) {
      // Reemplazar el badge por completo
      return '<span class="onsale flash-sale-badge">Flash Sale</span>';
    }

    return '<span class="onsale flash-sale-badge">Flash Sale</span>';
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

  public function admin_drop_sale_price_for_promotions()
  {
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

    // if ($promotion_end_time < current_time('timestamp')) {
    //   if (empty($products_of_promotion)) {
    //     $products_of_promotion = [];
    //   }

    //   $products_of_promotion = json_decode($products_of_promotion);

    //   $ids = wp_list_pluck($data['products'], 'id');

    //   $products = wc_get_products(['include' => $ids]);

    //   if (empty($products)) {
    //     return;
    //   }

    //   foreach ($products as $product) {
    //     // Eliminar el precio de oferta
    //     $product->set_sale_price(null);
    //     $product->set_price($product->get_regular_price());
    //     $product->save();
    //   }

    //   update_post_meta($id, '_promotion_active', 'no');
    // }
  }

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

    // var_dump('<pre>');
    // var_dump('timestamp');
    // var_dump(current_time('timestamp'));
    // var_dump('<pre>');

    // var_dump('<pre>');
    // var_dump('promotion_end_time');
    // var_dump($promotion_end_time);
    // var_dump('<pre>');

    // wp_die();

    if ($promotion_end_time < current_time('timestamp')) {
      if (empty($products_of_promotion)) {
        $products_of_promotion = [];
      }

      $ids = wp_list_pluck($data['products'], 'id');

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