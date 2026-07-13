<?php
namespace WooKasani\Admin\Metaboxes;

require_once WOO_KASANI_CONTROLLER_DIR . 'products.php';

use WooKasani\Admin\Controller\ProductControllers;

class Seeker_Promotions
{

  public function render_promotion_metabox($post)
  {
    wp_nonce_field('seeker_and_selector_nonce', 'seeker_and_selector_nonce');

    $products_selected_json = get_post_meta($post->ID, '_products_selected', true);
    $products_selected = json_decode($products_selected_json, true);

    if (!is_array($products_selected)) {
      $products_selected = [];
    }
    
    $html_products_selected = "";

    if(!empty($products_selected)) {
      $html_products_selected = ProductControllers::create_template_product_select_for_box($products_selected);
    }

    $products = ProductControllers::get_products([]);
    $products_selected_json_encoded = json_encode($products_selected);

    require_once WOO_KASANI_TEMPLATE_METABOX_DIR . 'seeker_promotion.php';
  }

  public function test_value($mixed)
  {
    var_dump('<pre>');
    var_dump($mixed);
    var_dump('</pre>');
    wp_die();
  }

  public function discount_of_products_metabox($post_id, $post)
  {
    // Verificar nonce
    if (!isset($_POST['seeker_and_selector_nonce']) || !wp_verify_nonce($_POST['seeker_and_selector_nonce'], 'seeker_and_selector_nonce')) {
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

    // Guardar los IDs de productos seleccionados
    if (isset($_POST['products_selected_for_seeker'])) {
      $data = sanitize_text_field($_POST['products_selected_for_seeker']);

      update_post_meta($post_id, '_products_selected', $data);
    } else {
        delete_post_meta($post_id, '_products_selected');
    }
  }

  public function ajax_get_products()
  {
    // Verificar nonce
    if (
      !isset($_POST['nonce']) ||
      !wp_verify_nonce(
        $_POST['nonce'],
        'seeker_and_selector_nonce'
      )
    ) {
      wp_send_json_error('Nonce inválido');
    }

    $pagina = isset($_POST['pagina']) ? intval($_POST['pagina']) : 1;
    $busqueda = isset($_POST['busqueda']) ? sanitize_text_field($_POST['busqueda']) : '';
    $por_pagina = 10;

    $args = [
      'posts_per_page' => $por_pagina,
      'paged' => $pagina
    ];

    if (!empty($busqueda)) {
      $args['s'] = $busqueda;
    }

    $query = ProductControllers::get_for_product_searched($args);
    $productos = ProductControllers::get_id_and_title_products($query);

    $respuesta = array(
      'productos' => $productos,
      'total_paginas' => $query->max_num_pages,
      'pagina_actual' => $pagina
    );

    wp_send_json_success($respuesta);
  }

  public function localize_script_logic_js($hook)
  {
    // Cargar solo en la pantalla de edición del CPT
    global $post;
    if ($hook != 'post.php' && $hook != 'post-new.php') {
      return;
    }
    if ($post && $post->post_type != 'promotions') {
      return;
    }

    // Pasar variables a JavaScript
    wp_localize_script(
      'woo_kasani_logic',
      'miMetabox',
      array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('seeker_and_selector_nonce'),
        'post_id' => $post ? $post->ID : 0
      )
    );
  }
}