<?php
namespace WooKasani\Admin\Controller;

use WP_Query;

class ProductControllers
{

  public static function get_products(array $args)
  {

    $search_product_args = array_merge([
      'limit' => -1,
      'status' => 'publish'
    ], $args);

    return wc_get_products($search_product_args);
  }

  public static function create_template_product_select_for_box(array $products_selected)
  {
    $html_products_selected = "";
    foreach ($products_selected as $product_selected) {

      if (empty($product_selected)) {
        continue;
      }

      $product_id = $product_selected['id'];
      $name = $product_selected['title'];

      $html_products_selected .= sprintf(
        "
          <span
            class='producto-tag'
            data-id='%d'
          >
            (ID: %d) %s
            <button
              type='button'
              class='eliminar-producto'
              data-id='%d'
            >
              ×
            </button>
          </span>
        ",
        $product_id,
        $product_id,
        esc_html($name),
        $product_id
      );
    }

    return $html_products_selected;
  }

  public static function get_for_product_searched(?array $args)
  {
    $product_args = array_merge([
      'post_type' => 'product', // Cambia si tus productos son otro CPT
      'post_status' => 'publish',
      'orderby' => 'title',
      'order' => 'ASC'
    ], $args);

    return new WP_Query($product_args);
  }

  public static function get_id_and_title_products(WP_Query $query)
  {
    $products = [];

    if ($query->have_posts()) {
      while ($query->have_posts()) {
        $query->the_post();
        $products[] = [
          'id' => get_the_ID(),
          'title' => get_the_title()
        ];
      }
      wp_reset_postdata();
    }

    return $products;
  }
}