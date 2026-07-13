<?php
namespace WooKasani\Includes;

require_once WOO_KASANI_ADMIN_DIR . 'class_admin.php';
require_once WOO_KASANI_ADMIN_DIR . 'load_dependencies.php';
require_once WOO_KASANI_PUBLIC_DIR . 'class_public.php';
require_once WOO_KASANI_PUBLIC_DIR . 'load_dependencies.php';
require_once WOO_KASANI_ADMIN_DIR . 'cpts.php';
require_once WOO_KASANI_ADMIN_METABOX_DIR . 'meta_boxes.php';
require_once WOO_KASANI_INCLUDES_DIR . 'loader.php';

use WooKasani\Admin\Admin;
use WooKasani\Admin\CPTS;
use WooKasani\Admin\MetaBoxes;
use WooKasani\Frontend\Frontend;

class Class_Woo_Kasani
{
  public $loader;
  private $frontend;
  private $admin;
  private $public_dependencies;
  private $admin_dependencies;
  private $cpts;
  private $meta_boxes;

  public function __construct()
  {
    $this->loader = new Loader();
    $this->frontend = new Frontend();
    $this->admin = new Admin();
    $this->public_dependencies = new \WooKasani\Frontend\Load_Dependencies();
    $this->admin_dependencies = new \WooKasani\Admin\Load_Dependencies();
    $this->cpts = new CPTS();
    $this->meta_boxes = new MetaBoxes();

    $this->custom_feature_frontend();
    $this->load_fns_admin();
    $this->load_dependencies_frontend();
    $this->load_dependencies_admin();
    $this->load_cpts();
    $this->load_metaboxes();
  }

  public function custom_feature_frontend()
  {
    $this->loader->add_action('wp_footer', $this->frontend, 'change_background_color');
    $this->loader->add_filter('custom_wlwl_stop_position', $this->frontend, 'win_new_user_a_cupon', 10, 3);
    $this->loader->add_filter('wlwl_add_message_of_success_design', $this->frontend, 'add_message_of_success_design', 10);
    $this->loader->add_filter('woocommerce_product_is_on_sale', $this->frontend, 'mi_producto_es_oferta_flash', 10, 2);
  }

  public function load_fns_admin() {
    $admin = $this->admin;
    
    $this->loader->add_action('admin_menu', $admin, 'add_menu');
    $this->loader->add_action('admin_enqueue_scripts', $admin, 'on_dependence');
    
    $this->loader->add_filter('manage_promotions_posts_columns', $admin, 'add_toggle_btn_to_promotions_columns', 10, 1);
    $this->loader->add_filter('manage_promotions_posts_custom_column', $admin, 'display_column_promotion', 10, 2);
    $this->loader->add_filter('wp_ajax_toggle_promotion', $admin, 'ajax_toggle_promotion', 10);

    $this->loader->add_filter('woocommerce_product_get_price', $admin, 'woo_kasani_apply_promotion_discount', 10, 2);
    $this->loader->add_filter('woocommerce_product_get_sale_price', $admin, 'woo_kasani_apply_promotion_discount', 10, 2);
    $this->loader->add_filter('woocommerce_product_variation_get_price', $admin, 'woo_kasani_apply_promotion_discount', 10, 2);
    $this->loader->add_filter('woocommerce_product_variation_get_sale_price', $admin, 'woo_kasani_apply_promotion_discount', 10, 2);

    $this->loader->add_filter('woocommerce_product_is_on_sale', $admin, 'woo_kasani_set_product_on_sale', 10, 2);
    $this->loader->add_filter('woocommerce_product_variation_is_on_sale', $admin, 'woo_kasani_set_product_on_sale', 10, 2);

    $this->loader->add_filter('woocommerce_sale_flash', $admin, 'woo_kasani_custom_sale_flash', 10, 3);
    $this->loader->add_filter('woocommerce_sale_badge_text', $admin, 'woo_kasani_custom_sale_flash_block_edit', 10, 2);

    $this->loader->add_action('save_post_promotions', $this->admin, 'admin_drop_sale_price_for_promotions', 10, 1);

    $this->loader->add_action('wp_ajax_deactivate_flash_promotion_nonce', $admin, 'frontend_drop_sale_price_for_promotions');
    $this->loader->add_action('wp_ajax_nopriv_deactivate_flash_promotion_nonce', $admin, 'frontend_drop_sale_price_for_promotions');
  }

  public function load_dependencies_frontend()
  {
    $public_dependencies = $this->public_dependencies;

    $this->loader->add_action('wp_enqueue_scripts', $public_dependencies, 'load_scripts');
    $this->loader->add_action('wp_enqueue_scripts', $public_dependencies, 'load_wp_localize_script');
    $this->loader->add_action('wp_enqueue_scripts', $public_dependencies, 'load_styles');
    $this->loader->add_action('wp_head', $public_dependencies, 'load_libraries');
  }

  public function load_dependencies_admin()
  {
    $admin_dependencies = $this->admin_dependencies;

    $this->loader->add_action('admin_enqueue_scripts', $admin_dependencies, 'load_scripts');
    $this->loader->add_action('wp_enqueue_scripts', $admin_dependencies, 'load_wp_localize_script');    
    $this->loader->add_action('admin_enqueue_scripts', $admin_dependencies, 'load_styles');
    $this->loader->add_action('admin_head', $admin_dependencies, 'load_libraries');
  }

  public function load_cpts()
  {
    $cpts = $this->cpts;

    $this->loader->add_action('init', $cpts, 'promotions');
  }

  public function load_metaboxes()
  {
    $meta_boxes = $this->meta_boxes;
    $loader = $this->loader;

    $loader->add_action('add_meta_boxes', $meta_boxes, 'add_meta_boxes');

    /**** SEEKER PRODUCTS ****/
    $loader->add_action('save_post_promotions', $meta_boxes, 'discount_of_products_metabox', 10, 2);

    $loader->add_action('wp_ajax_get_products_for_metabox', $meta_boxes, 'ajax_get_products');
    $loader->add_action('wp_ajax_nopriv_get_products_for_metabox', $meta_boxes, 'ajax_get_products');

    $loader->add_action('admin_enqueue_scripts', $meta_boxes, 'localize_script_logic_js');


    /**** DISCOUNT PRODUCTS ****/
    $loader->add_action('save_post_promotions', $meta_boxes, 'save_discount_metabox', 10, 2);


    /**** PROMOTIONS DURATION ****/
    $loader->add_action('save_post_promotions', $meta_boxes, 'save_promotion_duration_metabox', 10, 2);
    
    $loader->add_action('admin_notices', $meta_boxes, 'display_error_flash_sale');
    $loader->add_action('woocommerce_before_shop_loop', $meta_boxes, 'add_flash_warning_and_timer', 5);

    /**** SAVE DEFAULT VALUE OF META KEY PROMOTION ACTIVE ****/
    $loader->add_action('save_post_promotions', $meta_boxes, 'save_post_default_value_of_meta_key_promotion_active', 10, 1);
  }

  public function run()
  {
    $this->loader->run();
  }
}