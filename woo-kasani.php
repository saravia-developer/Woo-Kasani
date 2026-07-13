<?php
/**
 * Plugin Name: Woo Kasani
 * Author: Luis Saravia
 * Description: Woo Kasani is a plugin what allows you do customize your Woocommerce store and others plugins, wanted for Kasani S.A.
 * Version: 1.0.0
 */


defined( 'ABSPATH' ) || exit;

if (!function_exists('is_plugin_active')) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Comprueba si WooCommerce está ACTIVO (aunque sus clases aún no estén cargadas)
if (
  !is_plugin_active('woocommerce/woocommerce.php') ||
  !is_plugin_active('woocommerce-lucky-wheel/woocommerce-lucky-wheel.php')
) {
	deactivate_plugins(plugin_basename(__FILE__));

	wp_die(
		'Este plugin requiere que WooCommerce y Woocommerce Lucky Wheel estén activos. Por favor, actívalos primero.',
		'Dependencia faltante',
		['back_link' => true]
	);
}

define( 'WOO_KASANI_VERSION', '1.0.0' );

define( 'WOO_KASANI_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
define( 'WOO_KASANI_PUBLIC_DIR', WOO_KASANI_PLUGIN_DIR . 'public/' );
define( 'WOO_KASANI_ADMIN_DIR', WOO_KASANI_PLUGIN_DIR . 'admin/' );
define( 'WOO_KASANI_ADMIN_MENU_DIR', WOO_KASANI_ADMIN_DIR . 'menu/' );
define( 'WOO_KASANI_INCLUDES_DIR', WOO_KASANI_PLUGIN_DIR . 'includes/' );
define( 'WOO_KASANI_CONTROLLER_DIR', WOO_KASANI_ADMIN_DIR . 'controller/' );
define( 'WOO_KASANI_ADMIN_METABOX_DIR', WOO_KASANI_ADMIN_DIR . 'meta_boxes/' );
define( 'WOO_KASANI_TEMPLATE_DIR', WOO_KASANI_ADMIN_DIR . 'template/' );
define( 'WOO_KASANI_TEMPLATE_METABOX_DIR', WOO_KASANI_TEMPLATE_DIR . 'metabox/' );


define( 'WOO_KASANI_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define( 'WOO_KASANI_PUBLIC_URL', WOO_KASANI_PLUGIN_URL . 'public/' );
define( 'WOO_KASANI_ADMIN_URL', WOO_KASANI_PLUGIN_URL . 'admin/' );
define( 'WOO_KASANI_ADMIN_MENU_URL', WOO_KASANI_ADMIN_URL . 'menu/' );
define( 'WOO_KASANI_INCLUDES_URL', WOO_KASANI_PLUGIN_URL . 'includes/' );
define( 'WOO_KASANI_CONTROLLER_URL', WOO_KASANI_ADMIN_URL . 'controller/' );
define( 'WOO_KASANI_TEMPLATE_URL', WOO_KASANI_ADMIN_URL . 'template/' );
define( 'WOO_KASANI_TEMPLATE_METABOX_URL', WOO_KASANI_TEMPLATE_URL . 'metabox/' );

require_once WOO_KASANI_INCLUDES_DIR . 'class_woo_kasani.php';

$woo_kasani = new WooKasani\Includes\Class_Woo_Kasani();
$woo_kasani->run();