<?php
namespace WooKasani\Admin;

require_once WOO_KASANI_ADMIN_METABOX_DIR . 'seeker_promotions.php';
require_once WOO_KASANI_ADMIN_METABOX_DIR . 'discount_promotion.php';
require_once WOO_KASANI_ADMIN_METABOX_DIR . 'promotions_duration.php';


use WooKasani\Admin\Metaboxes\Promotions_Duration;
use WooKasani\Admin\Metaboxes\Seeker_Promotions;
use WooKasani\Admin\Metaboxes\Discount_Promotions;

class MetaBoxes
{
  private $seeker_promotions;
  private $discount_promotions;
  private $promotions_duration;

  public function __construct()
  {
    $this->seeker_promotions = new Seeker_Promotions();
    $this->discount_promotions = new Discount_Promotions();
    $this->promotions_duration = new Promotions_Duration();
  }


  public function add_meta_boxes()
  {
    add_meta_box(
      'promotion_details',
      'Promoción Detalles',
      [$this, 'render_promotion_metabox'],
      'promotions',
      'normal',
      'high'
    );

    add_meta_box(
      'discount',
      'Descuento',
      [$this, 'render_discount_metabox'],
      'promotions',
      'normal',
      'high'
    );

    add_meta_box(
      'promotion-duration',
      'Duración de la Promoción',
      [$this, 'render_promotion_duration_metabox'],
      'promotions',
      'normal',
      'high'
    );
  }

  /**** SEEKER PRODUCTS ****/
  public function render_promotion_metabox($post)
  {
    $this->seeker_promotions->render_promotion_metabox($post);
  }

  public function discount_of_products_metabox($post_id, $post)
  {
    $this->seeker_promotions->discount_of_products_metabox($post_id, $post);
  }

  public function ajax_get_products()
  {
    $this->seeker_promotions->ajax_get_products();
  }

  public function localize_script_logic_js($hook)
  {
    $this->seeker_promotions->localize_script_logic_js($hook);
  }


  /**** DISCOUNT PRODUCTS ****/
  public function render_discount_metabox($post)
  {
    $this->discount_promotions->render_discount_metabox($post);
  }

  public function save_discount_metabox($post_id, $post)
  {
    $this->discount_promotions->save_discount_metabox($post_id, $post);
  }


  /**** PROMOTIONS DURATION ****/
  public function render_promotion_duration_metabox($post)
  {
    $this->promotions_duration->render_promotion_duration_metabox($post);
  }

  public function save_promotion_duration_metabox($post_id, $post)
  {
    $this->promotions_duration->save_promotion_duration_metabox($post_id, $post);
  }

  public function save_post_default_value_of_meta_key_promotion_active($post_id) 
  {
    update_post_meta($post_id, '_promotion_active', 'no');
  }

  /**
   * Mostrar mensaje de advertencia y contador en la página de tienda
   */
  public function display_error_flash_sale()
  {
    $this->promotions_duration->display_error_flash_sale();
  }

  public function add_flash_warning_and_timer()
  {
    $this->promotions_duration->add_flash_warning_and_timer();
  }
}