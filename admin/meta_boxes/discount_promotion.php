<?php
namespace WooKasani\Admin\Metaboxes;

class Discount_Promotions
{

  public function render_discount_metabox($post)
  {
    wp_nonce_field('woo_kasani_discount_product_nonce', 'woo_kasani_discount_product_nonce');

    $all_types = wc_get_coupon_types() ?? [];
    $allowed   = ['percent', 'fixed_product'];
    $types_discount = array_intersect_key($all_types, array_flip($allowed));

    $type_discount_selected = get_post_meta($post->ID, '_select_type_discount', true);
    $discount_quantity = get_post_meta($post->ID, '_discount_quantity', true);

    if (empty($type_discount_selected))
      $type_discount_selected = 'percentaje';
    if (empty($discount_quantity))
      $discount_quantity = '';

    require_once WOO_KASANI_TEMPLATE_METABOX_DIR . 'discount_promotion.php';
  }

  public function save_discount_metabox($post_id, $post)
  {
    // Verificar nonce
    if (
      !isset($_POST['woo_kasani_discount_product_nonce']) ||
      !wp_verify_nonce($_POST['woo_kasani_discount_product_nonce'], 'woo_kasani_discount_product_nonce')
    ) {
      return;
    }

    // Verificar autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
    }

    // Verificar permisos
    if (!current_user_can('edit_post', $post_id)) {
      return;
    }

    // Guardar tipo de descuento
    if (isset($_POST['select_type_discount'])) {
      $type = sanitize_text_field($_POST['select_type_discount']);
      if (in_array($type, ['percent', 'fixed_product'])) {
        update_post_meta($post_id, '_select_type_discount', $type);
      }
    }

    // Guardar cantidad de descuento
    if (isset($_POST['products_group_discount'])) {
      $quantity = floatval($_POST['products_group_discount']);
      if ($quantity >= 0) {
        update_post_meta($post_id, '_discount_quantity', $quantity);
      } else {
        delete_post_meta($post_id, '_discount_quantity');
      }
    }
  }
}